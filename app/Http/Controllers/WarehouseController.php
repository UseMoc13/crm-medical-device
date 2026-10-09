<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    /**
     * Menampilkan daftar gudang.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query()
            ->withCount('inventories')
            ->withCount('stockMovements');

        // Pencarian berdasarkan kode atau nama gudang.
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'warehouse_code',
                    'ILIKE',
                    "%{$search}%"
                )->orWhere(
                    'warehouse_name',
                    'ILIKE',
                    "%{$search}%"
                );
            });
        }

        // Filter status gudang.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $warehouses = $query
            ->orderBy('warehouse_name')
            ->paginate(10)
            ->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Menampilkan form tambah gudang.
     */
    public function create()
    {
        $warehouse = new Warehouse();

        return view('warehouses.create', compact('warehouse'));
    }

    /**
     * Menyimpan gudang baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_code' => [
                'required',
                'string',
                'max:30',
                'unique:warehouses,warehouse_code',
            ],
            'warehouse_name' => [
                'required',
                'string',
                'max:100',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in(['Active', 'Inactive']),
            ],
        ], [
            'warehouse_code.required' => 'Kode gudang wajib diisi.',
            'warehouse_code.unique' => 'Kode gudang sudah digunakan.',
            'warehouse_code.max' => 'Kode gudang maksimal 30 karakter.',
            'warehouse_name.required' => 'Nama gudang wajib diisi.',
            'warehouse_name.max' => 'Nama gudang maksimal 100 karakter.',
            'status.required' => 'Status gudang wajib dipilih.',
            'status.in' => 'Status gudang tidak valid.',
        ]);

        Warehouse::create($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail gudang.
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->loadCount([
            'inventories',
            'stockMovements',
        ]);

        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Menampilkan form edit gudang.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Memperbarui data gudang.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'warehouse_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('warehouses', 'warehouse_code')
                    ->ignore(
                        $warehouse->warehouse_id,
                        'warehouse_id'
                    ),
            ],
            'warehouse_name' => [
                'required',
                'string',
                'max:100',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in(['Active', 'Inactive']),
            ],
        ], [
            'warehouse_code.required' => 'Kode gudang wajib diisi.',
            'warehouse_code.unique' => 'Kode gudang sudah digunakan.',
            'warehouse_code.max' => 'Kode gudang maksimal 30 karakter.',
            'warehouse_name.required' => 'Nama gudang wajib diisi.',
            'warehouse_name.max' => 'Nama gudang maksimal 100 karakter.',
            'status.required' => 'Status gudang wajib dipilih.',
            'status.in' => 'Status gudang tidak valid.',
        ]);

        $warehouse->update($validated);

        return redirect()
            ->route('warehouses.show', $warehouse)
            ->with('success', 'Data gudang berhasil diperbarui.');
    }

    /**
     * Menghapus gudang jika belum digunakan.
     */
    public function destroy(Warehouse $warehouse)
    {
        $hasInventory = $warehouse->inventories()->exists();

        $hasMovements = $warehouse->stockMovements()->exists();

        if ($hasInventory || $hasMovements) {
            return redirect()
                ->route('warehouses.index')
                ->with(
                    'error',
                    'Gudang tidak dapat dihapus karena sudah memiliki '
                    . 'data inventaris atau riwayat pergerakan stok.'
                );
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}