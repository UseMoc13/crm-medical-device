<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar customer.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'ILIKE', "%{$search}%")
                    ->orWhere('customer_name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%")
                    ->orWhere('city', 'ILIKE', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        $customers = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers'));
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

        $validated['customer_id'] = (string) Str::uuid();

        $validated['user_id'] = auth()->check()
            ? auth()->id()
            : null;

        // Generate customer code
        $validated['customer_code'] = $this->generateCustomerCode();

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail customer.
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'user',
            'contacts',
            'leads',
        ]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Form edit customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
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
            ->route('customers.show', $customer)
            ->with('success', 'Customer berhasil diperbarui.');
    }

    /**
     * Hapus customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * Generate kode customer.
     */
    private function generateCustomerCode()
    {
        do {
            $code = 'CUS-' . strtoupper(Str::random(6));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}