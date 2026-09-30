<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     */
    public function index(Request $request)
    {
        $query = Contact::with('customer');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('position', 'ILIKE', "%{$search}%")
                    ->orWhere('department', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('contact_type', 'ILIKE', "%{$search}%");
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
        | Filter Contact Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('contact_type')) {

            $query->where(
                'contact_type',
                $request->contact_type
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Filter Primary Contact
        |--------------------------------------------------------------------------
        */

        if ($request->filled('is_primary')) {

            $query->where(
                'is_primary',
                $request->is_primary
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'name',
            'position',
            'department',
            'contact_type',
            'created_at',
        ];

        $sort = $request->get('sort', 'created_at');

        $direction = $request->get('direction', 'desc');

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

        $contacts = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get([
                'customer_id',
                'customer_name',
                'customer_code',
            ]);


        $contactTypes = Contact::query()
            ->whereNotNull('contact_type')
            ->where('contact_type', '!=', '')
            ->distinct()
            ->orderBy('contact_type')
            ->pluck('contact_type');


        return view(
            'contacts.index',
            compact(
                'contacts',
                'customers',
                'contactTypes',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get([
                'customer_id',
                'customer_name',
                'customer_code',
                'phone',
                'email',
            ]);

        return view('contacts.create', compact('customers'));
    }


    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'position' => [
                'nullable',
                'string',
                'max:150',
            ],

            'department' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'contact_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'is_primary' => [
                'nullable',
                'boolean',
            ],

        ]);


        $validated['is_primary'] =
            $request->boolean('is_primary');


        DB::transaction(function () use ($validated) {

            /*
        |--------------------------------------------------------------------------
        | Jika contact baru menjadi primary,
        | nonaktifkan primary contact sebelumnya
        |--------------------------------------------------------------------------
        */

            if ($validated['is_primary']) {

                Contact::where(
                    'customer_id',
                    $validated['customer_id']
                )->update([
                    'is_primary' => false,
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Create Contact
        |--------------------------------------------------------------------------
        */

            Contact::create($validated);
        });


        return redirect()
            ->route('contacts.index')
            ->with(
                'success',
                'Contact berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        $contact->load('customer');

        return view(
            'contacts.show',
            compact('contact')
        );
    }


    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact)
    {
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
            'contacts.edit',
            compact(
                'contact',
                'customers'
            )
        );
    }


    /**
     * Update the specified contact.
     */
    public function update(
        Request $request,
        Contact $contact
    ) {

        $validated = $request->validate([

            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'position' => [
                'nullable',
                'string',
                'max:150',
            ],

            'department' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'contact_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'is_primary' => [
                'nullable',
                'boolean',
            ],

        ]);


        $validated['is_primary'] =
            $request->boolean('is_primary');


        DB::transaction(function () use (
            $validated,
            $contact
        ) {

            /*
        |--------------------------------------------------------------------------
        | Jika contact menjadi primary
        |--------------------------------------------------------------------------
        */

            if ($validated['is_primary']) {

                Contact::where(
                    'customer_id',
                    $validated['customer_id']
                )
                    ->where(
                        'contact_id',
                        '!=',
                        $contact->contact_id
                    )
                    ->update([
                        'is_primary' => false,
                    ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Update Contact
        |--------------------------------------------------------------------------
        */

            $contact->update($validated);
        });


        return redirect()
            ->route(
                'contacts.show',
                $contact
            )
            ->with(
                'success',
                'Data contact berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with(
                'success',
                'Contact berhasil dihapus.'
            );
    }
}
