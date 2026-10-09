<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar customer.
     */
    public function index(Request $request)
    {
        $query = Customer::query();


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('customer_code', 'ILIKE', "%{$search}%")
                    ->orWhere('customer_name', 'ILIKE', "%{$search}%")
                    ->orWhere('customer_type', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('city', 'ILIKE', "%{$search}%")
                    ->orWhere('province', 'ILIKE', "%{$search}%");

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Filter Customer Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_type')) {

            $query->where(
                'customer_type',
                $request->customer_type
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'customer_code',
            'customer_name',
            'customer_type',
            'phone',
            'city',
            'province',
            'status',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'created_at'
        );

        $direction = strtolower(
            $request->get(
                'direction',
                'desc'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Sort Column
        |--------------------------------------------------------------------------
        */

        if (!in_array($sort, $allowedSorts, true)) {

            $sort = 'created_at';

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Sort Direction
        |--------------------------------------------------------------------------
        */

        if (!in_array($direction, ['asc', 'desc'], true)) {

            $direction = 'desc';

        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $customers = $query
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $statuses = Customer::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');


        $customerTypes = Customer::query()
            ->whereNotNull('customer_type')
            ->where('customer_type', '!=', '')
            ->distinct()
            ->orderBy('customer_type')
            ->pluck('customer_type');


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('customers.index', compact(
            'customers',
            'statuses',
            'customerTypes',
            'sort',
            'direction'
        ));
    }


    /**
     * Form tambah customer.
     */
    public function create()
    {
        return view('customers.create');
    }


    /**
     * Menyimpan customer baru.
     */
    public function store(Request $request)
    {
        $number = DB::selectOne(
            "SELECT nextval('customer_code_seq') AS number"
        )->number;

        $validated['customer_code'] = 'CUST-' .
            str_pad((string) $number, 4, '0', STR_PAD_LEFT);

Customer::create($validated);
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:30'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Customer Code
        |--------------------------------------------------------------------------
        */

        

        if (
            $lastCustomer &&
            preg_match(
                '/CUST-(\d+)/',
                $lastCustomer->customer_code,
                $matches
            )
        ) {

            $number =
                ((int) $matches[1]) + 1;

        }


        $validated['customer_code'] =
            'CUST-' .
            str_pad(
                $number,
                4,
                '0',
                STR_PAD_LEFT
            );


        Customer::create($validated);


        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Customer berhasil ditambahkan.'
            );
    }


    /**
     * Menampilkan detail customer.
     */
    public function show(Customer $customer)
    {
        return view(
            'customers.show',
            compact('customer')
        );
    }


    /**
     * Form edit customer.
     */
    public function edit(Customer $customer)
    {
        return view(
            'customers.edit',
            compact('customer')
        );
    }


    /**
     * Update customer.
     */
    public function update(
        Request $request,
        Customer $customer
    ) {

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:30'],
        ]);


        $customer->update($validated);


        return redirect()
            ->route(
                'customers.show',
                $customer
            )
            ->with(
                'success',
                'Data customer berhasil diperbarui.'
            );
    }


    /**
     * Delete customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();


        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Customer berhasil dihapus.'
            );
    }
}