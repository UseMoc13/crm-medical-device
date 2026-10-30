<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WarrantyController extends Controller
{
    /**
     * Daftar status garansi.
     */
    private const WARRANTY_STATUSES = [
        'Active',
        'Expired',
        'Claimed',
        'Void',
    ];

    /**
     * Menampilkan daftar garansi.
     */
    public function index(Request $request)
    {
        $query = DB::table('warranties')
            ->join(
                'serial_numbers',
                'warranties.serial_number_id',
                '=',
                'serial_numbers.serial_number_id'
            )
            ->join(
                'customers',
                'warranties.customer_id',
                '=',
                'customers.customer_id'
            )
            ->leftJoin(
                'products',
                'serial_numbers.product_id',
                '=',
                'products.product_id'
            )
            ->select(
                'warranties.*',
                'serial_numbers.serial_number',
                'products.product_name',
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
                    'warranties.warranty_number',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'serial_numbers.serial_number',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'customers.customer_name',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'products.product_name',
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
                'warranties.status',
                $request->input('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Customer
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_id')) {
            $query->where(
                'warranties.customer_id',
                $request->input('customer_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengurutan
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'warranty_number' => 'warranties.warranty_number',
            'customer_name'   => 'customers.customer_name',
            'serial_number'   => 'serial_numbers.serial_number',
            'start_date'      => 'warranties.start_date',
            'end_date'        => 'warranties.end_date',
            'status'          => 'warranties.status',
            'created_at'      => 'warranties.created_at',
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

        $query->orderBy($allowedSorts[$sort], $direction);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $warranties = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Data Filter
        |--------------------------------------------------------------------------
        */

        $statuses = self::WARRANTY_STATUSES;

        $customers = DB::table('customers')
            ->select('customer_id', 'customer_name')
            ->orderBy('customer_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Garansi
        |--------------------------------------------------------------------------
        */

        $total = DB::table('warranties')->count();

        $active = DB::table('warranties')
            ->whereRaw(
                'LOWER(TRIM(status)) = ?',
                ['active']
            )
            ->whereDate('end_date', '>=', now()->toDateString())
            ->count();

        $expired = DB::table('warranties')
            ->where(function ($q) {
                $q->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['expired']
                )
                ->orWhereDate('end_date', '<', now()->toDateString());
            })
            ->count();

        $summary = [
            'total'   => $total,
            'active'  => $active,
            'expired' => $expired,
            'other'   => max(0, $total - $active - $expired),
        ];

        return view(
            'warranties.index',
            compact(
                'warranties',
                'statuses',
                'customers',
                'summary'
            )
        );
    }

    /**
     * Menampilkan form pembuatan garansi.
     */
    public function create()
    {
        $warrantyNumber = $this->generateWarrantyNumber();

        $serialNumbers = DB::table('serial_numbers')
            ->select('serial_number_id', 'serial_number', 'customer_id')
            ->orderBy('serial_number')
            ->get();

        $customers = DB::table('customers')
            ->select('customer_id', 'customer_name')
            ->orderBy('customer_name')
            ->get();

        $statuses = ['Active', 'Expired', 'Claimed', 'Void'];

        return view('warranties.create', compact(
            'warrantyNumber',
            'serialNumbers',
            'customers',
            'statuses'
        ));
    }

    /**
     * Menyimpan garansi baru.
     */
    public function store(Request $request)
    {
        $request->merge([
            'warranty_number' => trim(
                (string) $request->input('warranty_number')
            ),
            'status' => trim(
                (string) $request->input('status')
            ),
        ]);

        $validated = $request->validate([
            'warranty_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warranties', 'warranty_number'),
            ],

            'serial_number_id' => [
                'required',
                'uuid',
                'exists:serial_numbers,serial_number_id',
            ],

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'warranty_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                Rule::in(self::WARRANTY_STATUSES),
            ],

            'terms' => [
                'nullable',
                'string',
            ],
        ]);

    
                $serialNumber = DB::table('serial_numbers')
            ->where('serial_number_id', $validated['serial_number_id'])
            ->first();

        if (!$serialNumber || $serialNumber->customer_id !== $validated['customer_id']) {
            return back()
                ->withInput()
                ->withErrors([
                    'customer_id' => 'The selected customer does not match the selected serial number.',
                ]);
        }

        DB::table('warranties')->insert([
            'warranty_id' => (string) Str::uuid(),
            'serial_number_id' => $validated['serial_number_id'],
            'customer_id' => $validated['customer_id'],
            'warranty_number' => $validated['warranty_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'warranty_type' => $validated['warranty_type'] ?? null,
            'status' => $validated['status'],
            'terms' => $validated['terms'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('warranties.index')
            ->with('success', 'Warranty created successfully.');
    }

    /**
     * Menampilkan detail garansi.
     */
    public function show(string $warranty)
    {
        $warranty = DB::table('warranties')
            ->join(
                'serial_numbers',
                'warranties.serial_number_id',
                '=',
                'serial_numbers.serial_number_id'
            )
            ->join(
                'customers',
                'warranties.customer_id',
                '=',
                'customers.customer_id'
            )
            ->leftJoin(
                'products',
                'serial_numbers.product_id',
                '=',
                'products.product_id'
            )
            ->select(
                'warranties.*',
                'serial_numbers.serial_number',
                'products.product_name',
                'customers.customer_name'
            )
            ->where('warranties.warranty_id', $warranty)
            ->first();

        abort_if(!$warranty, 404);

        return view('warranties.show', compact('warranty'));
    }

    /**
     * Menampilkan form edit garansi.
     */
    public function edit(string $warranty)
    {
        $warranty = DB::table('warranties')
            ->where('warranty_id', $warranty)
            ->first();

        abort_if(!$warranty, 404);

        $serialNumbers = DB::table('serial_numbers')
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
                'serial_numbers.customer_id',
                'products.product_name',
                'customers.customer_name'
            )
            ->orderBy('serial_numbers.serial_number')
            ->get();

        $customers = DB::table('customers')
            ->select('customer_id', 'customer_name')
            ->orderBy('customer_name')
            ->get();

        $statuses = self::WARRANTY_STATUSES;

        return view(
            'warranties.edit',
            compact(
                'warranty',
                'serialNumbers',
                'customers',
                'statuses'
            )
        );
    }

    /**
     * Memperbarui data garansi.
     */
    public function update(Request $request, string $warranty)
    {
        $existingWarranty = DB::table('warranties')
            ->where('warranty_id', $warranty)
            ->first();

        abort_if(!$existingWarranty, 404);

        $request->merge([
            'warranty_number' => trim(
                (string) $request->input('warranty_number')
            ),
            'status' => trim(
                (string) $request->input('status')
            ),
        ]);

        $validated = $request->validate([
            'warranty_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warranties', 'warranty_number')
                    ->ignore($warranty, 'warranty_id'),
            ],

            'serial_number_id' => [
                'required',
                'uuid',
                'exists:serial_numbers,serial_number_id',
            ],

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'warranty_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                Rule::in(self::WARRANTY_STATUSES),
            ],

            'terms' => [
                'nullable',
                'string',
            ],
        ]);

        DB::table('warranties')
            ->where('warranty_id', $warranty)
            ->update([
                'serial_number_id' => $validated['serial_number_id'],
                'customer_id' => $validated['customer_id'],
                'warranty_number' => $validated['warranty_number'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'warranty_type' => $validated['warranty_type'] ?? null,
                'status' => $validated['status'],
                'terms' => $validated['terms'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('warranties.index')
            ->with('success', 'Warranty updated successfully.');
    }

    /**
     * Menghapus garansi.
     */
    public function destroy(string $warranty)
    {
        $existingWarranty = DB::table('warranties')
            ->where('warranty_id', $warranty)
            ->first();

        abort_if(!$existingWarranty, 404);

        DB::table('warranties')
            ->where('warranty_id', $warranty)
            ->delete();

        return redirect()
            ->route('warranties.index')
            ->with('success', 'Warranty deleted successfully.');
    }

    private function generateWarrantyNumber(): string
{
    $year = now()->format('Y');
    $prefix = 'WTY-' . $year . '-';

    $lastWarranty = DB::table('warranties')
        ->where('warranty_number', 'like', $prefix . '%')
        ->orderByRaw('CAST(SUBSTRING(warranty_number FROM ?) AS INTEGER) DESC', [
            strlen($prefix) + 1,
        ])
        ->first();

    $nextNumber = $lastWarranty
        ? ((int) substr($lastWarranty->warranty_number, strlen($prefix))) + 1
        : 1;

    return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
}
}