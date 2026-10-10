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
        $search = trim($request->input('search', ''));
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

        if (! array_key_exists($sort, $sortColumns)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $baseQuery = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.product_id')
            ->join('warehouses as w', 'sm.warehouse_id', '=', 'w.warehouse_id');

        // Search berdasarkan produk, gudang, referensi, atau catatan.
        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('p.product_code', 'ILIKE', "%{$search}%")
                    ->orWhere('p.product_name', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_code', 'ILIKE', "%{$search}%")
                    ->orWhere('w.warehouse_name', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.reference_type', 'ILIKE', "%{$search}%")
                    ->orWhere('sm.notes', 'ILIKE', "%{$search}%");
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

        // Summary mengikuti filter pencarian dan filter halaman.
        $summaryQuery = clone $baseQuery;

        $summary = [
            'total_transactions' => (clone $summaryQuery)->count('sm.stock_movement_id'),

            'total_quantity' => (clone $summaryQuery)->sum('sm.quantity'),

            'inbound' => (clone $summaryQuery)
                ->whereRaw("LOWER(sm.movement_type) IN (?, ?, ?, ?)", [
                    'in',
                    'inbound',
                    'stock_in',
                    'masuk',
                ])
                ->sum('sm.quantity'),

            'outbound' => (clone $summaryQuery)
                ->whereRaw("LOWER(sm.movement_type) IN (?, ?, ?, ?)", [
                    'out',
                    'outbound',
                    'stock_out',
                    'keluar',
                ])
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

        // Ambil tipe yang benar-benar digunakan di database.
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
        $products = \Illuminate\Support\Facades\DB::table('products')
            ->select(
                'product_id',
                'product_code',
                'product_name',
                'unit'
            )
            ->orderBy('product_name')
            ->get();

        $warehouses = \Illuminate\Support\Facades\DB::table('warehouses')
            ->select(
                'warehouse_id',
                'warehouse_code',
                'warehouse_name'
            )
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
            'product_id' => [
                'required',
                'uuid',
                'exists:products,product_id',
            ],
            'warehouse_id' => [
                'required',
                'uuid',
                'exists:warehouses,warehouse_id',
            ],
            'movement_type' => [
                'required',
                'in:opening_stock,stock_in,stock_out',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'reference_type' => [
                'nullable',
                'string',
                'max:50',
            ],
            'reference_id' => [
                'nullable',
                'uuid',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
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

            /*
        |--------------------------------------------------------------------------
        | Stock Opening
        |--------------------------------------------------------------------------
        | Menetapkan saldo awal produk pada gudang.
        */
            if ($type === 'opening_stock') {
                if ($inventory && (int) $inventory->quantity > 0) {
                    throw ValidationException::withMessages([
                        'movement_type' =>
                        'Stok untuk produk dan gudang ini sudah tersedia. Gunakan Stock In untuk menambah stok.',
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
            }

            /*
        |--------------------------------------------------------------------------
        | Stock In
        |--------------------------------------------------------------------------
        | Menambahkan jumlah stok yang masuk.
        */ elseif ($type === 'stock_in') {
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
            }

            /*
        |--------------------------------------------------------------------------
        | Stock Out
        |--------------------------------------------------------------------------
        | Mengurangi stok jika saldo mencukupi.
        */ else {
                if (
                    ! $inventory ||
                    (int) $inventory->quantity < $quantity
                ) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                        'Stok tidak mencukupi untuk transaksi Stock Out.',
                    ]);
                }

                DB::table('inventories')
                    ->where('inventory_id', $inventory->inventory_id)
                    ->update([
                        'quantity' => (int) $inventory->quantity - $quantity,
                        'updated_at' => $now,
                    ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Record Movement History
        |--------------------------------------------------------------------------
        */
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
        });

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement berhasil disimpan.');
    }

    /**
     * Menampilkan halaman edit stock movement.
     */
    public function edit(string $stockMovement)
    {
        $movement = DB::table('stock_movements as sm')
            ->join('products as p', 'sm.product_id', '=', 'p.product_id')
            ->join('warehouses as w', 'sm.warehouse_id', '=', 'w.warehouse_id')
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
                'p.unit',
                'w.warehouse_code',
                'w.warehouse_name',
            ])
            ->where('sm.stock_movement_id', $stockMovement)
            ->first();

        abort_if(! $movement, 404, 'Stock movement tidak ditemukan.');

        return view('stock-movements.edit', compact('movement'));
    }

    /**
     * Memperbarui stock movement sekaligus menyesuaikan saldo inventaris.
     *
     * Produk, gudang, dan tipe transaksi tidak diubah dari halaman edit.
     */
    public function update(Request $request, string $stockMovement)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $stockMovement) {
            $movement = DB::table('stock_movements')
                ->where('stock_movement_id', $stockMovement)
                ->lockForUpdate()
                ->first();

            if (! $movement) {
                abort(404, 'Stock movement tidak ditemukan.');
            }

            $inventory = DB::table('inventories')
                ->where('product_id', $movement->product_id)
                ->where('warehouse_id', $movement->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'quantity' => 'Data inventaris untuk transaksi ini tidak ditemukan. Stok tidak dapat diperbarui dengan aman.',
                ]);
            }

            $oldQuantity = (int) $movement->quantity;
            $newQuantity = (int) $validated['quantity'];
            $currentStock = (int) $inventory->quantity;
            $difference = $newQuantity - $oldQuantity;

            /*
             * Hitung perubahan saldo berdasarkan tipe transaksi.
             * Stock In: perubahan jumlah sama dengan selisih transaksi.
             * Stock Out: perubahan jumlah berlawanan dengan selisih transaksi.
             * Opening Stock: koreksi saldo awal berdasarkan selisih.
             */
            switch ($movement->movement_type) {
                case 'opening_stock':
                case 'stock_in':
                    $newStock = $currentStock + $difference;
                    break;

                case 'stock_out':
                    $newStock = $currentStock - $difference;
                    break;

                default:
                    throw ValidationException::withMessages([
                        'quantity' => 'Tipe transaksi ini belum didukung untuk proses edit.',
                    ]);
            }

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Perubahan ditolak karena saldo stok tidak mencukupi. Periksa transaksi stok berikutnya.',
                ]);
            }

            DB::table('inventories')
                ->where('inventory_id', $inventory->inventory_id)
                ->update([
                    'quantity' => $newStock,
                    'updated_at' => now(),
                ]);

            DB::table('stock_movements')
                ->where('stock_movement_id', $stockMovement)
                ->update([
                    'quantity' => $newQuantity,
                    'reference_type' => $validated['reference_type'] ?? null,
                    'reference_id' => $validated['reference_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
        });

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement berhasil diperbarui dan saldo inventaris telah disesuaikan.');
    }
    public function destroy(string $stockMovement)
    {
        DB::transaction(function () use ($stockMovement) {
            $movement = DB::table('stock_movements')
                ->where('stock_movement_id', $stockMovement)
                ->lockForUpdate()
                ->first();

            if (! $movement) {
                abort(404, 'Stock movement tidak ditemukan.');
            }

            $inventory = DB::table('inventories')
                ->where('product_id', $movement->product_id)
                ->where('warehouse_id', $movement->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'stock_movement' => 'Data inventaris tidak ditemukan. Transaksi tidak dihapus agar konsistensi data tetap terjaga.',
                ]);
            }

            $currentStock = (int) $inventory->quantity;
            $quantity = (int) $movement->quantity;

            switch ($movement->movement_type) {
                case 'opening_stock':
                case 'stock_in':
                    $newStock = $currentStock - $quantity;
                    break;

                case 'stock_out':
                    $newStock = $currentStock + $quantity;
                    break;

                default:
                    throw ValidationException::withMessages([
                        'stock_movement' => 'Tipe transaksi ini belum didukung untuk proses penghapusan.',
                    ]);
            }

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'stock_movement' => 'Transaksi tidak dapat dihapus karena stok saat ini tidak mencukupi untuk membatalkan transaksi tersebut.',
                ]);
            }

            DB::table('inventories')
                ->where('inventory_id', $inventory->inventory_id)
                ->update([
                    'quantity' => $newStock,
                    'updated_at' => now(),
                ]);

            DB::table('stock_movements')
                ->where('stock_movement_id', $stockMovement)
                ->delete();
        });

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement berhasil dihapus dan saldo inventaris telah disesuaikan.');
    }
}
