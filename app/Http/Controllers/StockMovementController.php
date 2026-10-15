<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $movementType = $request->input('movement_type', '');
        $warehouseId = $request->input('warehouse_id', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $sortColumns = [
            'created_at' => 'sm.created_at',
            'product_code' => 'p.product_code',
            'product_name' => 'p.product_name',
            'warehouse_name' => 'w.warehouse_name',
            'movement_type' => 'sm.movement_type',
            'quantity' => 'sm.quantity',
        ];

        $sort = $request->input('sort', 'created_at');
        $direction = strtolower($request->input('direction', 'desc'));

        if (!array_key_exists($sort, $sortColumns)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $baseQuery = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.product_id')
            ->join('warehouses as w', 'sm.warehouse_id', '=', 'w.warehouse_id');

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('sm.stock_movement_id', '::text', 'ILIKE', "%{$search}%")
                    ->orWhere('p.product_code', 'ILIKE', "%{$search}%")
                    ->orWhere('p.product_name', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_code', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_name', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.reference_type', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.notes', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.correction_reason', 'ILIKE', "%{$search}%");
            });
        }

        if ($movementType !== '') {
            $baseQuery->where('sm.movement_type', $movementType);
        }

        if ($warehouseId !== '') {
            $baseQuery->where('sm.warehouse_id', $warehouseId);
        }

        if ($dateFrom !== '') {
            $baseQuery->whereDate('sm.created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $baseQuery->whereDate('sm.created_at', '<=', $dateTo);
        }

        $summary = [
            'total_transactions' => (clone $baseQuery)->count('sm.stock_movement_id'),
            'total_quantity' => (clone $baseQuery)->sum('sm.quantity'),
            'inbound' => (clone $baseQuery)
                ->whereIn('sm.movement_type', ['opening_stock', 'stock_in'])
                ->sum('sm.quantity'),
            'outbound' => (clone $baseQuery)
                ->where('sm.movement_type', 'stock_out')
                ->sum('sm.quantity'),
        ];

        $movements = $baseQuery
            ->select([
                'sm.stock_movement_id',
                'sm.warehouse_id',
                'sm.product_id',
                'sm.movement_type',
                'sm.quantity',
                'sm.reference_type',
                'sm.reference_id',
                'sm.notes',
                'sm.created_at',
                'sm.reversal_of_id',
                'sm.reversed_by_id',
                'sm.correction_reason',
                'p.product_code',
                'p.product_name',
                'p.product_type',
                'p.unit',
                'w.warehouse_code',
                'w.warehouse_name',
            ])
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('sm.stock_movement_id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $warehouses = DB::table('warehouses')
            ->select('warehouse_id', 'warehouse_code', 'warehouse_name')
            ->orderBy('warehouse_name')
            ->get();

        $movementTypes = DB::table('stock_movements')
            ->whereNotNull('movement_type')
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');

        return view('stock-movements.index', compact(
            'movements',
            'warehouses',
            'movementTypes',
            'summary',
            'search',
            'movementType',
            'warehouseId',
            'dateFrom',
            'dateTo',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $products = DB::table('products')
            ->select('product_id', 'product_code', 'product_name', 'unit')
            ->orderBy('product_name')
            ->get();

        $warehouses = DB::table('warehouses')
            ->select('warehouse_id', 'warehouse_code', 'warehouse_name')
            ->orderBy('warehouse_name')
            ->get();

        return view('stock-movements.create', compact(
            'products',
            'warehouses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,product_id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,warehouse_id'],
            'movement_type' => ['required', 'in:opening_stock,stock_in,stock_out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated) {
            $now = now();
            $quantity = (int) $validated['quantity'];
            $type = $validated['movement_type'];

            $inventory = DB::table('inventories')
                ->where('product_id', $validated['product_id'])
                ->where('warehouse_id', $validated['warehouse_id'])
                ->lockForUpdate()
                ->first();

            if ($type === 'opening_stock') {
                $openingExists = DB::table('stock_movements')
                    ->where('product_id', $validated['product_id'])
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->where('movement_type', 'opening_stock')
                    ->exists();

                if ($openingExists) {
                    throw ValidationException::withMessages([
                        'movement_type' => 'Stok awal untuk produk dan gudang ini sudah pernah dicatat. Gunakan Stock In untuk menambah stok.',
                    ]);
                }

                if ($inventory) {
                    DB::table('inventories')
                        ->where('inventory_id', $inventory->inventory_id)
                        ->update([
                            'quantity' => $quantity,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('inventories')->insert([
                        'inventory_id' => (string) Str::uuid(),
                        'product_id' => $validated['product_id'],
                        'warehouse_id' => $validated['warehouse_id'],
                        'quantity' => $quantity,
                        'minimum_stock' => null,
                        'updated_at' => $now,
                    ]);
                }
            } elseif ($type === 'stock_in') {
                if ($inventory) {
                    DB::table('inventories')
                        ->where('inventory_id', $inventory->inventory_id)
                        ->update([
                            'quantity' => (int) $inventory->quantity + $quantity,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('inventories')->insert([
                        'inventory_id' => (string) Str::uuid(),
                        'product_id' => $validated['product_id'],
                        'warehouse_id' => $validated['warehouse_id'],
                        'quantity' => $quantity,
                        'minimum_stock' => null,
                        'updated_at' => $now,
                    ]);
                }
            } else {
                if (!$inventory || (int) $inventory->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stok tidak mencukupi untuk transaksi Stock Out.',
                    ]);
                }

                DB::table('inventories')
                    ->where('inventory_id', $inventory->inventory_id)
                    ->update([
                        'quantity' => (int) $inventory->quantity - $quantity,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('stock_movements')->insert([
                'stock_movement_id' => (string) Str::uuid(),
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'movement_type' => $type,
                'quantity' => $quantity,
                'reference_type' => $validated['reference_type']
                    ?? ($type === 'opening_stock' ? 'opening_stock' : null),
                'reference_id' => $validated['reference_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_at' => $now,
            ]);
        }, 3);

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement berhasil disimpan.');
    }

    public function show(string $stockMovement)
    {
        $movement = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.product_id')
            ->join('warehouses as w', 'sm.warehouse_id', '=', 'w.warehouse_id')
            ->leftJoin('stock_movements as reversal', 'sm.reversed_by_id', '=', 'reversal.stock_movement_id')
            ->leftJoin('inventories as i', function ($join) {
                $join->on('i.product_id', '=', 'sm.product_id')
                    ->on('i.warehouse_id', '=', 'sm.warehouse_id');
            })
            ->select([
                'sm.stock_movement_id',
                'sm.product_id',
                'sm.warehouse_id',
                'sm.movement_type',
                'sm.quantity',
                'sm.reference_type',
                'sm.reference_id',
                'sm.notes',
                'sm.created_at',
                'sm.reversal_of_id',
                'sm.reversed_by_id',
                'sm.correction_reason',
                'p.product_code',
                'p.product_name',
                'p.product_type',
                'p.unit',
                'w.warehouse_code',
                'w.warehouse_name',
                'i.quantity as current_stock',
                'reversal.stock_movement_id as reversal_movement_id',
                'reversal.movement_type as reversal_movement_type',
                'reversal.quantity as reversal_quantity',
                'reversal.created_at as reversal_created_at',
            ])
            ->where('sm.stock_movement_id', $stockMovement)
            ->first();

        abort_if(!$movement, 404, 'Stock movement tidak ditemukan.');

        $relatedOriginalId = $movement->reversal_of_id
            ?: ($movement->reference_type === 'correction'
                ? $movement->reference_id
                : $movement->stock_movement_id);

        $originalMovement = null;

        if ($relatedOriginalId !== $movement->stock_movement_id) {
            $originalMovement = DB::table('stock_movements')
                ->where('stock_movement_id', $relatedOriginalId)
                ->first();
        }

        $replacementMovement = null;

        if ($relatedOriginalId) {
            $replacementMovement = DB::table('stock_movements')
                ->where('reference_type', 'correction')
                ->where('reference_id', $relatedOriginalId)
                ->whereNull('reversal_of_id')
                ->first();

            if (
                $replacementMovement
                && $replacementMovement->stock_movement_id === $movement->stock_movement_id
            ) {
                $replacementMovement = null;
            }
        }

        return view('stock-movements.show', compact(
            'movement',
            'originalMovement',
            'replacementMovement'
        ));
    }

    public function createCorrection(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $movementType = (string) $request->query('movement_type', '');
        $warehouseId = (string) $request->query('warehouse_id', '');
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');

        $eligibleQuery = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.product_id')
            ->join('warehouses as w', 'sm.warehouse_id', '=', 'w.warehouse_id')
            ->whereIn('sm.movement_type', ['stock_in', 'stock_out'])
            ->whereNull('sm.reversal_of_id')
            ->whereNull('sm.reversed_by_id');

        $selectedId = $request->query('stock_movement_id');
        $selectedMovement = null;

        if ($selectedId) {
            $selectedMovement = (clone $eligibleQuery)
                ->select([
                    'sm.stock_movement_id',
                    'sm.product_id',
                    'sm.warehouse_id',
                    'sm.movement_type',
                    'sm.quantity',
                    'sm.reference_type',
                    'sm.reference_id',
                    'sm.notes',
                    'sm.created_at',
                    'p.product_code',
                    'p.product_name',
                    'p.product_type',
                    'p.unit',
                    'w.warehouse_code',
                    'w.warehouse_name',
                ])
                ->where('sm.stock_movement_id', $selectedId)
                ->first();

            abort_if(
                !$selectedMovement,
                404,
                'Transaksi tidak ditemukan, bukan transaksi yang dapat dikoreksi, atau sudah pernah dikoreksi.'
            );
        }

        $movementsQuery = (clone $eligibleQuery);

        if ($search !== '') {
            $movementsQuery->where(function ($query) use ($search) {
                $query->whereRaw('CAST(sm.stock_movement_id AS TEXT) ILIKE ?', ["%{$search}%"])
                    ->orWhere('p.product_code', 'ILIKE', "%{$search}%")
                    ->orWhere('p.product_name', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_code', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_name', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.notes', 'ILIKE', "%{$search}%");
            });
        }

        if (in_array($movementType, ['stock_in', 'stock_out'], true)) {
            $movementsQuery->where('sm.movement_type', $movementType);
        } else {
            $movementType = '';
        }

        if ($warehouseId !== '') {
            $movementsQuery->where('sm.warehouse_id', $warehouseId);
        }

        if ($dateFrom !== '') {
            $movementsQuery->whereDate('sm.created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $movementsQuery->whereDate('sm.created_at', '<=', $dateTo);
        }

        $movements = $movementsQuery
            ->select([
                'sm.stock_movement_id',
                'sm.product_id',
                'sm.warehouse_id',
                'sm.movement_type',
                'sm.quantity',
                'sm.created_at',
                'p.product_code',
                'p.product_name',
                'p.unit',
                'w.warehouse_code',
                'w.warehouse_name',
            ])
            ->orderByDesc('sm.created_at')
            ->orderByDesc('sm.stock_movement_id')
            ->paginate(10)
            ->withQueryString();

        $warehouses = DB::table('warehouses')
            ->select('warehouse_id', 'warehouse_code', 'warehouse_name')
            ->orderBy('warehouse_name')
            ->get();

        return view('stock-movements.correction-create', compact(
            'movements',
            'selectedMovement',
            'warehouses',
            'search',
            'movementType',
            'warehouseId',
            'dateFrom',
            'dateTo'
        ));
    }

    public function storeCorrection(Request $request, string $stockMovement)
    {
        $validated = $request->validate([
            'movement_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'correction_reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $stockMovement) {
            $original = DB::table('stock_movements')
                ->where('stock_movement_id', $stockMovement)
                ->lockForUpdate()
                ->first();

            if (!$original) {
                abort(404, 'Stock movement tidak ditemukan.');
            }

            if (!in_array($original->movement_type, ['stock_in', 'stock_out'], true)) {
                throw ValidationException::withMessages([
                    'stock_movement' => 'Hanya transaksi Stock In dan Stock Out yang dapat dikoreksi melalui alur ini.',
                ]);
            }

            if ($original->reversal_of_id || $original->reversed_by_id) {
                throw ValidationException::withMessages([
                    'stock_movement' => 'Transaksi ini merupakan transaksi pembalik atau sudah pernah dikoreksi.',
                ]);
            }

            $inventory = DB::table('inventories')
                ->where('product_id', $original->product_id)
                ->where('warehouse_id', $original->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'stock_movement' => 'Data inventaris untuk transaksi ini tidak ditemukan. Koreksi dibatalkan.',
                ]);
            }

            $oldQuantity = (int) $original->quantity;
            $newQuantity = (int) $validated['quantity'];
            $currentStock = (int) $inventory->quantity;

            $oldDelta = $original->movement_type === 'stock_in'
                ? $oldQuantity
                : -$oldQuantity;

            $newDelta = $validated['movement_type'] === 'stock_in'
                ? $newQuantity
                : -$newQuantity;

            $newStock = $currentStock - $oldDelta + $newDelta;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Koreksi ditolak karena saldo stok akan menjadi negatif. Periksa transaksi berikutnya atau gunakan kuantitas yang sesuai.',
                ]);
            }

            $now = now();
            $reversalId = (string) Str::uuid();
            $replacementId = (string) Str::uuid();

            $reversalType = $original->movement_type === 'stock_in'
                ? 'stock_out'
                : 'stock_in';

            DB::table('stock_movements')->insert([
                'stock_movement_id' => $reversalId,
                'product_id' => $original->product_id,
                'warehouse_id' => $original->warehouse_id,
                'movement_type' => $reversalType,
                'quantity' => $oldQuantity,
                'reference_type' => 'correction_reversal',
                'reference_id' => $original->stock_movement_id,
                'notes' => 'Reversal of stock movement ' . $original->stock_movement_id,
                'created_at' => $now,
                'reversal_of_id' => $original->stock_movement_id,
                'correction_reason' => $validated['correction_reason'],
            ]);

            DB::table('stock_movements')->insert([
                'stock_movement_id' => $replacementId,
                'product_id' => $original->product_id,
                'warehouse_id' => $original->warehouse_id,
                'movement_type' => $validated['movement_type'],
                'quantity' => $newQuantity,
                'reference_type' => 'correction',
                'reference_id' => $original->stock_movement_id,
                'notes' => $validated['notes'] ?? null,
                'created_at' => $now,
                'correction_reason' => $validated['correction_reason'],
            ]);

            DB::table('stock_movements')
                ->where('stock_movement_id', $original->stock_movement_id)
                ->update([
                    'reversed_by_id' => $reversalId,
                    'correction_reason' => $validated['correction_reason'],
                ]);

            DB::table('inventories')
                ->where('inventory_id', $inventory->inventory_id)
                ->update([
                    'quantity' => $newStock,
                    'updated_at' => $now,
                ]);
        }, 3);

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Koreksi berhasil dicatat. Transaksi lama tetap tersimpan dan saldo inventaris telah disesuaikan.');
    }
}
