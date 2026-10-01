<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpportunityController extends Controller
{
    /**
     * Display a listing of opportunities.
     */
    public function index(Request $request)
    {
        $query = Opportunity::with([
            'customer',
            'lead',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('opportunity_code', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhere('stage', 'ILIKE', "%{$search}%")
                    ->orWhere('status', 'ILIKE', "%{$search}%");
            });
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
        | Filter Sales / User
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
        | Filter Stage
        |--------------------------------------------------------------------------
        */

        if ($request->filled('stage')) {

            $query->where(
                'stage',
                $request->stage
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
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'opportunity_code',
            'name',
            'stage',
            'estimated_value',
            'expected_close_date',
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

        $opportunities = $query
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

        $stages = Opportunity::query()
            ->whereNotNull('stage')
            ->where('stage', '!=', '')
            ->distinct()
            ->orderBy('stage')
            ->pluck('stage');

        $statuses = Opportunity::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view(
            'opportunities.index',
            compact(
                'opportunities',
                'users',
                'customers',
                'stages',
                'statuses',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new opportunity.
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

        $leads = Lead::query()
            ->orderBy('company_name')
            ->get([
                'lead_id',
                'lead_code',
                'company_name',
                'contact_name',
                'customer_id',
                'user_id',
            ]);

        return view(
            'opportunities.create',
            compact(
                'users',
                'customers',
                'leads'
            )
        );
    }


    /**
     * Store a newly created opportunity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'lead_id' => [
                'nullable',
                'uuid',
                'exists:leads,lead_id',
            ],

            'user_id' => [
                'required',
                'uuid',
                'exists:users,user_id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'stage' => [
                'required',
                'string',
                'max:100',
            ],

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Opportunity Code
        |--------------------------------------------------------------------------
        */

        $validated['opportunity_code'] =
            $this->generateOpportunityCode();

        Opportunity::create(
            $validated
        );

        return redirect()
            ->route('opportunities.index')
            ->with(
                'success',
                'Opportunity berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified opportunity.
     */
    public function show(Opportunity $opportunity)
    {
        $opportunity->load([
            'customer',
            'lead',
            'user',
            'items.product',
        ]);

        return view(
            'opportunities.show',
            compact('opportunity')
        );
    }


    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(Opportunity $opportunity)
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
            $opportunity->user_id &&
            !$users->contains(
                'user_id',
                $opportunity->user_id
            )
        ) {

            $currentUser = User::query()
                ->where(
                    'user_id',
                    $opportunity->user_id
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

        $leads = Lead::query()
            ->orderBy('company_name')
            ->get([
                'lead_id',
                'lead_code',
                'company_name',
                'contact_name',
                'customer_id',
                'user_id',
            ]);

        return view(
            'opportunities.edit',
            compact(
                'opportunity',
                'users',
                'customers',
                'leads'
            )
        );
    }


    /**
     * Update the specified opportunity.
     */
    public function update(
        Request $request,
        Opportunity $opportunity
    ) {

        $validated = $request->validate([

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'lead_id' => [
                'nullable',
                'uuid',
                'exists:leads,lead_id',
            ],

            'user_id' => [
                'required',
                'uuid',
                'exists:users,user_id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'stage' => [
                'required',
                'string',
                'max:100',
            ],

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $opportunity->update(
            $validated
        );

        return redirect()
            ->route(
                'opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                'Data opportunity berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified opportunity.
     */
    public function destroy(Opportunity $opportunity)
    {
        $opportunity->delete();

        return redirect()
            ->route('opportunities.index')
            ->with(
                'success',
                'Opportunity berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Opportunity Code
    |--------------------------------------------------------------------------
    */

    private function generateOpportunityCode()
    {
        do {

            $code =
                'OPP-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(5)
                );

        } while (
            Opportunity::where(
                'opportunity_code',
                $code
            )->exists()
        );

        return $code;
    }
}