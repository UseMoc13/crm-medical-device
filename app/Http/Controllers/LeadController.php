<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $query = Lead::with([
            'user',
            'customer',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('lead_code', 'ILIKE', "%{$search}%")
                    ->orWhere('company_name', 'ILIKE', "%{$search}%")
                    ->orWhere('contact_name', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('source', 'ILIKE', "%{$search}%")
                    ->orWhere('status', 'ILIKE', "%{$search}%");
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Filter User / Sales
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {

            $query->where(
                'user_id',
                $request->user_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Filter Customer
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_id')) {

            $query->where(
                'customer_id',
                $request->customer_id
            );
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
        | Filter Source
        |--------------------------------------------------------------------------
        */

        if ($request->filled('source')) {

            $query->where(
                'source',
                $request->source
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'lead_code',
            'company_name',
            'contact_name',
            'source',
            'status',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'created_at'
        );

        $direction = $request->get(
            'direction',
            'desc'
        );


        if (!in_array($sort, $allowedSorts)) {

            $sort = 'created_at';
        }


        if (!in_array($direction, ['asc', 'desc'])) {

            $direction = 'desc';
        }


        $query->orderBy(
            $sort,
            $direction
        );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $leads = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $users = User::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'user_id',
                'name',
                'email',
            ]);


        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get([
                'customer_id',
                'customer_name',
                'customer_code',
            ]);


        $statuses = Lead::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');


        $sources = Lead::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');


        return view(
            'leads.index',
            compact(
                'leads',
                'users',
                'customers',
                'statuses',
                'sources',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $users = User::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'user_id',
                'name',
                'email',
            ]);


        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get([
                'customer_id',
                'customer_name',
                'customer_code',
                'phone',
                'email',
            ]);


        return view(
            'leads.create',
            compact(
                'users',
                'customers'
            )
        );
    }


    /**
     * Store a newly created lead.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'user_id' => [
                'nullable',
                'uuid',
                'exists:users,user_id',
            ],

            'customer_id' => [
                'nullable',
                'uuid',
                'exists:customers,customer_id',
            ],

            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'source' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],

            'qualification' => [
                'nullable',
                'string',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Lead Code
        |--------------------------------------------------------------------------
        */

        $validated['lead_code'] =
            $this->generateLeadCode();


        Lead::create($validated);


        return redirect()
            ->route('leads.index')
            ->with(
                'success',
                'Lead berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'user',
            'customer',
        ]);


        return view(
            'leads.show',
            compact('lead')
        );
    }


    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        $users = User::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'user_id',
                'name',
                'email',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Include Current User
        |--------------------------------------------------------------------------
        */

        if (
            $lead->user_id &&
            !$users->contains(
                'user_id',
                $lead->user_id
            )
        ) {

            $currentUser = User::query()
                ->where(
                    'user_id',
                    $lead->user_id
                )
                ->first([
                    'user_id',
                    'name',
                    'email',
                ]);


            if ($currentUser) {

                $users->push(
                    $currentUser
                );
            }
        }


        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get([
                'customer_id',
                'customer_name',
                'customer_code',
                'phone',
                'email',
            ]);


        return view(
            'leads.edit',
            compact(
                'lead',
                'users',
                'customers'
            )
        );
    }


    /**
     * Update the specified lead.
     */
    public function update(
        Request $request,
        Lead $lead
    ) {

        $validated = $request->validate([

            'user_id' => [
                'nullable',
                'uuid',
                'exists:users,user_id',
            ],

            'customer_id' => [
                'nullable',
                'uuid',
                'exists:customers,customer_id',
            ],

            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'source' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],

            'qualification' => [
                'nullable',
                'string',
            ],

        ]);


        $lead->update(
            $validated
        );


        return redirect()
            ->route(
                'leads.show',
                $lead
            )
            ->with(
                'success',
                'Data lead berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified lead.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();


        return redirect()
            ->route('leads.index')
            ->with(
                'success',
                'Lead berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Lead Code
    |--------------------------------------------------------------------------
    */

    private function generateLeadCode()
    {
        do {

            $code =
                'LEAD-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(5)
                );

        } while (
            Lead::where(
                'lead_code',
                $code
            )->exists()
        );


        return $code;
    }
}