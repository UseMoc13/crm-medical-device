<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CrmDataScope;
use App\Services\CrmPermissionResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar customer.
     */
    public function index(Request $request)
    {
        $query = $this->visibleCustomers();


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

        $statuses = $this->visibleCustomers()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');


        $customerTypes = $this->visibleCustomers()
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
    // 1. Validasi input terlebih dahulu
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

    // 2. When scoped access is explicitly enabled, assign new customer
    // records to the signed-in user for own/branch scopes. Unscoped
    // environments retain the existing nullable owner behavior.
    if ($this->customerDataScopeEnabled()) {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(401);
        }
        $resolver = app(CrmPermissionResolver::class);
        $scopes = $resolver->scopes($user, 'crm_page.customers.view');
        // Fail closed before generating a Customer Code if View has no
        // configured row scope. A branch-only user also needs a branch.
        $canSeeNewRecord = in_array('all', $scopes, true)
            || in_array('own', $scopes, true)
            || (in_array('branch', $scopes, true) && $user->branch_id !== null);
        abort_unless($resolver->allows($user, 'crm_page.customers.view') && $canSeeNewRecord, 403);

        if (! in_array('all', $scopes, true)) {
            $validated['user_id'] = $user->getKey();
        }
    }

    // 3. Buat customer code menggunakan sequence PostgreSQL
    $number = DB::selectOne(
        "SELECT nextval('customer_code_seq') AS number"
    )->number;

    $validated['customer_code'] = 'CUST-' .
        str_pad((string) $number, 4, '0', STR_PAD_LEFT);

    // 4. Simpan data hanya satu kali
    Customer::create($validated);

    // 5. Kembali ke daftar customer
    return redirect()
        ->route('customers.index')
        ->with('success', 'Customer berhasil ditambahkan.');
}



    /**
     * Menampilkan detail customer.
     */
    public function show(Customer $customer)
    {
        $this->assertVisibleCustomer($customer);

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
        $this->assertVisibleCustomer($customer);

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
        $this->assertVisibleCustomer($customer);

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
        $this->assertVisibleCustomer($customer);

        $customer->delete();


        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Customer berhasil dihapus.'
            );
    }
    /**
     * A6.11 opt-in pilot. Disabled by default: no changes in current access
     * until the application owner explicitly enables this environment flag.
     */
    private function customerDataScopeEnabled(): bool
    {
        return (bool) config('crm_data_scope.customers_enabled', false);
    }

    private function visibleCustomers(): Builder
    {
        $query = Customer::query();
        if (! $this->customerDataScopeEnabled()) {
            return $query;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(401);
        }

        return app(CrmDataScope::class)->applyForPermission(
            $query,
            $user,
            'customers',
            'crm_page.customers.view',
            app(CrmPermissionResolver::class)
        );
    }

    /**
     * A6.11 direct UUID authorization; deny with 404 so inaccessible records
     * cannot be enumerated by trying the show/edit/update/delete endpoints.
     * Existing route-level CRUD permission checks remain in force.
     */
    private function assertVisibleCustomer(Customer $customer): void
    {
        if (! $this->customerDataScopeEnabled()) {
            return;
        }

        abort_unless(
            $this->visibleCustomers()->whereKey($customer->getKey())->exists(),
            404
        );
    }
}