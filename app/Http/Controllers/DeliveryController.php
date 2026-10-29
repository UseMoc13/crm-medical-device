<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    /**
     * Menampilkan daftar pengiriman.
     */
    public function index()
    {
        $deliveries = Delivery::with('salesOrder')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('deliveries.index', compact('deliveries'));
    }

    /**
     * Menampilkan form tambah pengiriman.
     */
    public function create()
    {
        $salesOrders = SalesOrder::orderByDesc('order_date')->get();

        return view('deliveries.create', compact('salesOrders'));
    }

    /**
     * Menyimpan pengiriman baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => [
                'required',
                'uuid',
                'exists:sales_orders,sales_order_id',
            ],
            'delivery_number' => [
                'required',
                'string',
                'max:255',
                'unique:deliveries,delivery_number',
            ],
            'delivery_date' => [
                'nullable',
                'date',
            ],
            'delivery_address' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['delivery_id'] = (string) Str::uuid();

        Delivery::create($validated);

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Delivery berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pengiriman.
     */
    public function show(Delivery $delivery)
    {
        $delivery->load('salesOrder');

        return view('deliveries.show', compact('delivery'));
    }

    /**
     * Menampilkan form edit pengiriman.
     */
    public function edit(Delivery $delivery)
    {
        $salesOrders = SalesOrder::orderByDesc('order_date')->get();

        return view(
            'deliveries.edit',
            compact('delivery', 'salesOrders')
        );
    }

    /**
     * Memperbarui data pengiriman.
     */
    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'sales_order_id' => [
                'required',
                'uuid',
                'exists:sales_orders,sales_order_id',
            ],
            'delivery_number' => [
                'required',
                'string',
                'max:255',
                'unique:deliveries,delivery_number,'
                    . $delivery->delivery_id
                    . ',delivery_id',
            ],
            'delivery_date' => [
                'nullable',
                'date',
            ],
            'delivery_address' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $delivery->update($validated);

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Delivery berhasil diperbarui.');
    }

    /**
     * Menghapus pengiriman.
     */
    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Delivery berhasil dihapus.');
    }
}