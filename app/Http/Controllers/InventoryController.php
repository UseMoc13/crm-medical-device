<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Daftar warehouse untuk filter.
        $warehouses = DB::table('warehouses')
            ->select('warehouse_id', 'warehouse_code', 'warehouse_name')
            ->orderBy('warehouse_name')
            ->get();

        // Ringkasan inventory secara keseluruhan.
        $summary = DB::table('inventories')
            ->selectRaw('COUNT(*) AS total_records')
            ->selectRaw('COALESCE(SUM(quantity), 0) AS total_quantity')
            ->selectRaw(
                'COALESCE(SUM(CASE
                    WHEN quantity <= 0 THEN 1
                    ELSE 0
                END), 0) AS out_of_stock'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE
                    WHEN quantity > 0
                    AND minimum_stock IS NOT NULL
                    AND quantity <= minimum_stock
                    THEN 1
                    ELSE 0
                END), 0) AS low_stock'
            )
            ->first();

        // Kolom yang boleh digunakan untuk sorting.
        $sortColumns = [
            'product_code'   => 'p.product_code',
            'product_name'   => 'p.product_name',
            'warehouse_name' => 'w.warehouse_name',
            'quantity'       => 'i.quantity',
            'minimum_stock'  => 'i.minimum_stock',
            'updated_at'     => 'i.updated_at',
        ];

        $sort = $request->query('sort', 'updated_at');

        if (!array_key_exists($sort, $sortColumns)) {
            $sort = 'updated_at';
        }

        $direction = strtolower($request->query('direction', 'desc'));

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        // Query inventory beserta informasi produk dan warehouse.
        $query = DB::table('inventories as i')
            ->join(
                'products as p',
                'i.product_id',
                '=',
                'p.product_id'
            )
            ->join(
                'warehouses as w',
                'i.warehouse_id',
                '=',
                'w.warehouse_id'
            )
            ->select([
                'i.inventory_id',
                'i.warehouse_id',
                'i.product_id',
                'i.quantity',
                'i.minimum_stock',
                'i.updated_at',
                'p.product_code',
                'p.product_name',
                'p.product_type',
                'p.unit',
                'p.status as product_status',
                'w.warehouse_code',
                'w.warehouse_name',
                'w.status as warehouse_status',
            ]);

        // Pencarian produk, kode produk, dan warehouse.
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $keyword = '%' . $search . '%';

                $builder
                    ->where('p.product_name', 'ILIKE', $keyword)
                    ->orWhere('p.product_code', 'ILIKE', $keyword)
                    ->orWhere('w.warehouse_name', 'ILIKE', $keyword)
                    ->orWhere('w.warehouse_code', 'ILIKE', $keyword);
            });
        }

        // Filter berdasarkan warehouse.
        $warehouseId = $request->query('warehouse_id');

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->where('i.warehouse_id', $warehouseId);
        }

        // Filter kondisi stok.
        $stockStatus = $request->query('stock_status');

        switch ($stockStatus) {
            case 'out_of_stock':
                $query->where('i.quantity', '<=', 0);
                break;

            case 'low_stock':
                $query
                    ->where('i.quantity', '>', 0)
                    ->whereNotNull('i.minimum_stock')
                    ->whereRaw('i.quantity <= i.minimum_stock');
                break;

            case 'in_stock':
                $query
                    ->where('i.quantity', '>', 0)
                    ->whereNotNull('i.minimum_stock')
                    ->whereRaw('i.quantity > i.minimum_stock');
                break;

            case 'no_minimum':
                $query->whereNull('i.minimum_stock');
                break;
        }

        // Pengurutan dan pagination.
        $inventories = $query
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('i.inventory_id')
            ->paginate(10)
            ->withQueryString();

        return view('inventories.index', compact(
            'inventories',
            'warehouses',
            'summary',
            'search',
            'warehouseId',
            'stockStatus',
            'sort',
            'direction'
        ));
    }
}
