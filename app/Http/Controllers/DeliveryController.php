<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\SalesOrder;
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
        $delivery->load('salesOrder');
        return view('deliveries.show', compact('delivery'));
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
            if ($validated['status'] === 'delivered' && empty($locked->delivery_date)) {
                $locked->delivery_date = today();
            }
            $locked->status = $validated['status'];
            $locked->save();
        });
        return redirect()->route('deliveries.show', $delivery)->with('success', 'Delivery status updated.');
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
