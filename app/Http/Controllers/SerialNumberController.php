<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SerialNumberController extends Controller
{
    /**
     * Daftar status lifecycle serial number.
     */
    private const SERIAL_STATUSES = [
        'Available',
        'Reserved',
        'In Delivery',
        'Pending Installation',
        'Installed',
        'Under Maintenance',
        'Out of Service',
        'Retired',
    ];

    /**
     * Menampilkan daftar serial number.
     */
    public function index(Request $request)
    {
        $query = DB::table('serial_numbers')
            ->leftJoin(
                'products',
                'serial_numbers.product_id',
                '=',
                'products.product_id'
            )
            ->leftJoin(
                'customers',
                'serial_numbers.customer_id',
                '=',
                'customers.customer_id'
            )
            ->select(
                'serial_numbers.serial_number_id',
                'serial_numbers.serial_number',
                'serial_numbers.product_id',
                'serial_numbers.customer_id',
                'serial_numbers.status',
                'serial_numbers.purchase_date',
                'serial_numbers.created_at',
                'products.product_name',
                'products.product_code',
                'customers.customer_name'
            );

        /*
        |--------------------------------------------------------------------------
        | Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where(
                    'serial_numbers.serial_number',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'products.product_name',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'products.product_code',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'customers.customer_name',
                    'ILIKE',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'serial_numbers.status',
                $request->input('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Produk
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product_id')) {
            $query->where(
                'serial_numbers.product_id',
                $request->input('product_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Customer
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_id')) {
            $query->where(
                'serial_numbers.customer_id',
                $request->input('customer_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengurutan
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'created_at'    => 'serial_numbers.created_at',
            'serial_number' => 'serial_numbers.serial_number',
            'product_name'  => 'products.product_name',
            'purchase_date' => 'serial_numbers.purchase_date',
            'status'        => 'serial_numbers.status',
        ];

        $sort = $request->input('sort', 'created_at');

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $direction = strtolower(
            $request->input('direction', 'desc')
        );

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy(
            $allowedSorts[$sort],
            $direction
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $serialNumbers = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Data Filter
        |--------------------------------------------------------------------------
        */

        // Menggunakan daftar status tetap agar konsisten.
        $statuses = self::SERIAL_STATUSES;

        $products = DB::table('products')
            ->select(
                'product_id',
                'product_name',
                'product_code'
            )
            ->orderBy('product_name')
            ->get();

        $customers = DB::table('customers')
            ->select(
                'customer_id',
                'customer_name'
            )
            ->orderBy('customer_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Serial Number
        |--------------------------------------------------------------------------
        */

        $total = DB::table('serial_numbers')->count();

        $assigned = DB::table('serial_numbers')
            ->whereNotNull('customer_id')
            ->count();

        $available = DB::table('serial_numbers')
            ->whereRaw(
                'LOWER(TRIM(status)) = ?',
                ['available']
            )
            ->count();

        $summary = [
            'total'     => $total,
            'assigned'  => $assigned,
            'available' => $available,
            'other'     => max(
                0,
                $total - $assigned - $available
            ),
        ];

        return view(
            'serial-numbers.index',
            compact(
                'serialNumbers',
                'statuses',
                'products',
                'customers',
                'summary'
            )
        );
    }

    /**
     * Menampilkan form pembuatan serial number.
     */
    public function create()
    {
        $products = DB::table('products')
            ->select(
                'product_id',
                'product_name',
                'product_code'
            )
            ->orderBy('product_name')
            ->get();

        $customers = DB::table('customers')
            ->select(
                'customer_id',
                'customer_name'
            )
            ->orderBy('customer_name')
            ->get();

        // Status tetap tersedia meskipun belum ada data serial number.
        $statuses = self::SERIAL_STATUSES;

        return view(
            'serial-numbers.create',
            compact(
                'products',
                'customers',
                'statuses'
            )
        );
    }

    /**
     * Menyimpan serial number baru.
     */
    public function store(Request $request)
    {
        $request->merge([
            'serial_number' => trim(
                (string) $request->input('serial_number')
            ),
            'status' => trim(
                (string) $request->input('status')
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validasi Input
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'serial_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'serial_numbers',
                    'serial_number'
                ),
            ],

            'product_id' => [
                'required',
                'uuid',
                'exists:products,product_id',
            ],

            'customer_id' => [
                'nullable',
                'uuid',
                'exists:customers,customer_id',
            ],

            'status' => [
                'required',
                'string',
                Rule::in(self::SERIAL_STATUSES),
            ],

            'purchase_date' => [
                'nullable',
                'date',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Simpan Data ke PostgreSQL
        |--------------------------------------------------------------------------
        */

        DB::table('serial_numbers')->insert([
            'serial_number_id' => (string) Str::uuid(),
            'product_id'       => $validated['product_id'],
            'customer_id'      => $validated['customer_id'] ?? null,
            'serial_number'    => $validated['serial_number'],
            'status'           => $validated['status'],
            'purchase_date'    => $validated['purchase_date'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()
            ->route('serial-numbers.index')
            ->with(
                'success',
                'Serial number created successfully.'
            );
    }
}
