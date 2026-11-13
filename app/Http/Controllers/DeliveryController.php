<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\SalesOrder;
use App\Models\SerialNumber;
use App\Models\DeliveryItem;
use App\Models\Inventory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    private const TRANSITIONS = [
        'pending' => ['in_transit', 'cancelled'],
        'in_transit' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:pending,in_transit,delivered,cancelled'],
            'sort' => ['nullable', 'in:created_at,delivery_date,delivery_number,status'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $query = Delivery::query()->with('salesOrder');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('delivery_number', 'ilike', "%{$escaped}%")
                    ->orWhere('delivery_address', 'ilike', "%{$escaped}%")
                    ->orWhereHas('salesOrder', function ($order) use ($escaped) {
                        $order->where('sales_order_number', 'ilike', "%{$escaped}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $deliveries = $query->orderBy($sort, $direction)
            ->orderBy('delivery_id', 'desc')
            ->paginate(10)->withQueryString();

        return view('deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        $salesOrders = SalesOrder::whereNotIn('status', ['cancelled', 'rejected'])
            ->orderByDesc('order_date')->get();
        $deliveryNumber = Delivery::generateDeliveryNumber();
        $previousAddresses = $this->previousDeliveryAddresses();
        return view('deliveries.create', compact('salesOrders', 'deliveryNumber', 'previousAddresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'uuid', 'exists:sales_orders,sales_order_id'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $order = SalesOrder::whereKey($validated['sales_order_id'])->lockForUpdate()->firstOrFail();
            if (in_array(strtolower((string) $order->status), ['cancelled', 'rejected'], true)) {
                throw ValidationException::withMessages(['sales_order_id' => 'A cancelled Sales Order cannot be shipped.']);
            }
            Delivery::create([
                ...$validated,
                'delivery_number' => Delivery::generateDeliveryNumber(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('deliveries.index')->with('success', 'Delivery created with Pending status.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['salesOrder.items.product']);
        $trackingAvailable = Schema::hasTable('delivery_items') && Schema::hasColumn('delivery_items', 'warehouse_id');
        if ($trackingAvailable) {
            $delivery->load(['items.serialNumber.product', 'items.warehouse']);
        } else {
            $delivery->setRelation('items', collect());
        }
        $availableSerials = collect();
        $warehouseStocks = collect();
        $dispatchMovements = collect();
        $orderProducts = collect();
        if ($delivery->status !== 'pending') {
            $dispatchMovements = DB::table('stock_movements as sm')
                ->join('warehouses as w', 'w.warehouse_id', '=', 'sm.warehouse_id')
                ->join('products as p', 'p.product_id', '=', 'sm.product_id')
                ->where('sm.reference_type', 'delivery')
                ->where('sm.reference_id', $delivery->getKey())
                ->where('sm.movement_type', 'stock_out')
                ->select('w.warehouse_name', 'p.product_name', 'sm.quantity', 'sm.created_at')
                ->orderBy('sm.created_at')->get();
        }
        if ($trackingAvailable && $delivery->salesOrder) {
            $orderProducts = $delivery->salesOrder->items->groupBy('product_id')->map(fn ($items) => [
                'name' => optional($items->first()->product)->product_name ?? $items->first()->product_id,
                'quantity' => (int) $items->sum('quantity'),
            ]);
            $warehouseStocks = Inventory::query()->with('warehouse')
                ->whereIn('product_id', $orderProducts->keys())
                ->where('quantity', '>', 0)
                ->orderBy('warehouse_id')->get();
            $availableSerials = SerialNumber::query()->with('product')
                ->whereIn('product_id', $orderProducts->keys())
                ->where('status', 'available')
                ->whereNotIn('serial_number_id', DeliveryItem::query()
                    ->select('delivery_items.serial_number_id')
                    ->join('deliveries', 'deliveries.delivery_id', '=', 'delivery_items.delivery_id')
                    ->where('deliveries.status', '!=', 'cancelled'))
                ->orderBy('serial_number')->get();
        }
        $serialProductMap = $availableSerials->pluck('product_id', 'serial_number_id')->all();
        return view('deliveries.show', compact('delivery', 'trackingAvailable', 'availableSerials', 'orderProducts', 'warehouseStocks', 'dispatchMovements', 'serialProductMap'));
    }

    public function addItem(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'serial_number_id' => ['required', 'uuid', 'exists:serial_numbers,serial_number_id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,warehouse_id'],
        ]);
        if (!Schema::hasTable('delivery_items') || !Schema::hasColumn('delivery_items', 'warehouse_id')) {
            return back()->with('error', 'Delivery Items table has not been created. Run the new migration first.');
        }
        DB::transaction(function () use ($delivery, $validated) {
            $locked = Delivery::whereKey($delivery->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['serial_number_id' => 'Items can only be changed while Delivery is Pending.']);
            }
            $serial = SerialNumber::whereKey($validated['serial_number_id'])->lockForUpdate()->firstOrFail();
            if ($serial->status !== 'available') {
                throw ValidationException::withMessages(['serial_number_id' => 'This serial number is not available.']);
            }
            $orders = SalesOrder::whereKey($locked->sales_order_id)->lockForUpdate()->firstOrFail();
            $orderQuantity = (int) $orders->items()->where('product_id', $serial->product_id)->sum('quantity');
            if ($orderQuantity < 1) {
                throw ValidationException::withMessages(['serial_number_id' => 'This product is not included in the Sales Order.']);
            }
            $stock = Inventory::query()
                ->where('product_id', $serial->product_id)
                ->where('warehouse_id', $validated['warehouse_id'])
                ->first();
            if (!$stock || $stock->quantity < 1) {
                throw ValidationException::withMessages(['warehouse_id' => 'There is no stock of this product in the selected warehouse.']);
            }
            $taken = DeliveryItem::query()->join('deliveries', 'deliveries.delivery_id', '=', 'delivery_items.delivery_id')
                ->where('delivery_items.serial_number_id', $serial->getKey())
                ->where('deliveries.status', '!=', 'cancelled')->exists();
            if ($taken) {
                throw ValidationException::withMessages(['serial_number_id' => 'This serial number is already assigned to another active Delivery.']);
            }
            $allocated = DeliveryItem::query()->join('deliveries', 'deliveries.delivery_id', '=', 'delivery_items.delivery_id')
                ->join('serial_numbers', 'serial_numbers.serial_number_id', '=', 'delivery_items.serial_number_id')
                ->where('deliveries.sales_order_id', $locked->sales_order_id)
                ->where('deliveries.status', '!=', 'cancelled')
                ->where('serial_numbers.product_id', $serial->product_id)
                ->sum('delivery_items.quantity');
            if ((int) $allocated >= $orderQuantity) {
                throw ValidationException::withMessages(['serial_number_id' => 'The ordered quantity has already been allocated to Deliveries.']);
            }
            DeliveryItem::create([
                'delivery_id' => $locked->getKey(),
                'serial_number_id' => $serial->getKey(),
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => 1,
            ]);
        });
        return redirect()->route('deliveries.show', $delivery)->with('success', 'Serial number added to Delivery.');
    }

    public function removeItem(Delivery $delivery, DeliveryItem $item)
    {
        DB::transaction(function () use ($delivery, $item) {
            $locked = Delivery::whereKey($delivery->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending' || $item->delivery_id !== $locked->getKey()) {
                throw ValidationException::withMessages(['delivery_item' => 'Only items from a Pending Delivery can be removed.']);
            }
            DeliveryItem::whereKey($item->getKey())->delete();
        });
        return redirect()->route('deliveries.show', $delivery)->with('success', 'Serial number removed from Delivery.');
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load('salesOrder');
        if (in_array($delivery->status, ['delivered', 'cancelled'], true)) {
            return redirect()->route('deliveries.show', $delivery)->with('error', 'Finalized deliveries cannot be edited.');
        }
        $salesOrders = SalesOrder::orderByDesc('order_date')->get();
        $previousAddresses = $this->previousDeliveryAddresses();
        return view('deliveries.edit', compact('delivery', 'salesOrders', 'previousAddresses'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'delivery_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        DB::transaction(function () use ($delivery, $validated) {
            $locked = Delivery::whereKey($delivery->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['delivered', 'cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'This delivery is already finalized.']);
            }
            $locked->update($validated);
        });
        return redirect()->route('deliveries.show', $delivery)->with('success', 'Delivery details updated.');
    }

    public function changeStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate(['status' => ['required', 'in:in_transit,delivered,cancelled']]);
        DB::transaction(function () use ($delivery, $validated) {
            $locked = Delivery::whereKey($delivery->getKey())->lockForUpdate()->firstOrFail();
            $allowed = self::TRANSITIONS[$locked->status] ?? [];
            if (!in_array($validated['status'], $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'This status transition is not allowed.']);
            }
            if ($validated['status'] === 'in_transit') {
                $this->dispatchAllocatedUnits($locked);
            }
            if ($validated['status'] === 'delivered' && empty($locked->delivery_date)) {
                $locked->delivery_date = today();
            }
            $locked->status = $validated['status'];
            $locked->save();
        });
        return redirect()->route('deliveries.show', $delivery)->with('success', 'Delivery status updated.');
    }

    /**
     * Dispatch is atomic: inventory, stock movement and serial status change together.
     * Warehouse is recorded on each Delivery Item so provenance is explicit.
     */
    private function dispatchAllocatedUnits(Delivery $delivery): void
    {
        if (!Schema::hasTable('delivery_items') || !Schema::hasColumn('delivery_items', 'warehouse_id')) {
            throw ValidationException::withMessages(['status' => 'Please run the Delivery Items warehouse migration first.']);
        }

        $items = DeliveryItem::query()->where('delivery_id', $delivery->getKey())
            ->orderBy('delivery_item_id')->lockForUpdate()->get();
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['status' => 'Allocate at least one serial-numbered unit before dispatch.']);
        }
        if ($items->contains(fn ($item) => !$item->warehouse_id || (int) $item->quantity !== 1)) {
            throw ValidationException::withMessages(['status' => 'Each allocated serial number needs a warehouse and quantity of one. Re-add legacy unassigned items.']);
        }
        if ($items->pluck('serial_number_id')->unique()->count() !== $items->count()) {
            throw ValidationException::withMessages(['status' => 'Duplicate serial numbers detected.']);
        }
        if (DB::table('stock_movements')->where('reference_type', 'delivery')
            ->where('reference_id', $delivery->getKey())->where('movement_type', 'stock_out')->exists()) {
            throw ValidationException::withMessages(['status' => 'This Delivery already has stock-out movements; dispatch cannot run again.']);
        }

        $order = SalesOrder::query()->whereKey($delivery->sales_order_id)->firstOrFail();
        $ordered = $order->items()->select('product_id')->selectRaw('SUM(quantity) AS total_quantity')
            ->groupBy('product_id')->pluck('total_quantity', 'product_id');
        $serials = SerialNumber::query()->whereIn('serial_number_id', $items->pluck('serial_number_id'))
            ->orderBy('serial_number_id')->lockForUpdate()->get()->keyBy('serial_number_id');

        $groups = [];
        foreach ($items as $item) {
            $serial = $serials->get($item->serial_number_id);
            if (!$serial || $serial->status !== 'available' || !isset($ordered[$serial->product_id])) {
                throw ValidationException::withMessages(['status' => 'One or more allocated devices are no longer available or do not belong to the Sales Order.']);
            }
            $key = $item->warehouse_id . ':' . $serial->product_id;
            if (!isset($groups[$key])) $groups[$key] = ['warehouse_id' => $item->warehouse_id, 'product_id' => $serial->product_id, 'quantity' => 0];
            $groups[$key]['quantity']++;
        }

        $allOrderItems = DeliveryItem::query()
            ->join('deliveries', 'deliveries.delivery_id', '=', 'delivery_items.delivery_id')
            ->join('serial_numbers', 'serial_numbers.serial_number_id', '=', 'delivery_items.serial_number_id')
            ->where('deliveries.sales_order_id', $delivery->sales_order_id)
            ->where('deliveries.status', '!=', 'cancelled')
            ->select('serial_numbers.product_id')->selectRaw('SUM(delivery_items.quantity) as qty')
            ->groupBy('serial_numbers.product_id')->pluck('qty', 'product_id');
        foreach ($allOrderItems as $productId => $quantity) {
            if ((int) $quantity > (int) ($ordered[$productId] ?? 0)) {
                throw ValidationException::withMessages(['status' => 'Allocated units exceed the Sales Order quantity.']);
            }
        }

        usort($groups, fn ($a, $b) => strcmp($a['warehouse_id'].':'.$a['product_id'], $b['warehouse_id'].':'.$b['product_id']));
        $now = now();
        foreach ($groups as $group) {
            $inventory = Inventory::query()->where('warehouse_id', $group['warehouse_id'])
                ->where('product_id', $group['product_id'])->lockForUpdate()->first();
            if (!$inventory || $inventory->quantity < $group['quantity']) {
                throw ValidationException::withMessages(['status' => 'Insufficient warehouse stock to dispatch all allocated units.']);
            }
            $inventory->quantity -= $group['quantity'];
            $inventory->updated_at = $now;
            $inventory->save();
            DB::table('stock_movements')->insert([
                'stock_movement_id' => (string) Str::uuid(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'warehouse_id' => $group['warehouse_id'],
                'product_id' => $group['product_id'],
                'movement_type' => 'stock_out',
                'quantity' => $group['quantity'],
                'reference_type' => 'delivery',
                'reference_id' => $delivery->getKey(),
                'notes' => 'Auto dispatch: ' . $delivery->delivery_number,
                'created_at' => $now,
            ]);
        }
        foreach ($serials as $serial) {
            $serial->status = 'assigned';
            $serial->save();
        }
    }

    public function destroy(Delivery $delivery)
    {
        $deleted = DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending' || DB::table('installations')->where('delivery_id', $locked->getKey())->exists()) {
                return false;
            }
            $locked->delete();
            return true;
        });
        if (!$deleted) {
            return redirect()->route('deliveries.index')->with('error', 'This Delivery cannot be deleted. Only Pending deliveries without Installation records can be deleted.');
        }
        return redirect()->route('deliveries.index')->with('success', 'Delivery deleted successfully.');
    }

    private function previousDeliveryAddresses()
    {
        return Delivery::query()
            ->whereNotNull('delivery_address')
            ->whereRaw("BTRIM(delivery_address) <> ''")
            ->select('delivery_address')
            ->selectRaw('MAX(created_at) as latest_used_at')
            ->groupBy('delivery_address')
            ->orderByDesc('latest_used_at')
            ->limit(30)
            ->pluck('delivery_address');
    }

}
