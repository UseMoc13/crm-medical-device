<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with([
            'quotation.opportunity.customer'
        ])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'sales_order_number',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'status',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhereHas('quotation', function ($quotation) use ($search) {
                    $quotation->where(
                        'quotation_number',
                        'ILIKE',
                        "%{$search}%"
                    );
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query
            ->paginate(10)
            ->withQueryString();

        return view('sales_orders.index', compact('orders'));
    }

    
        public function storeFromQuotation(string $quotationId)
        {
            DB::transaction(function () use ($quotationId) {
                $quotation = DB::table('quotations')
                    ->where('quotation_id', $quotationId)
                    ->lockForUpdate()
                    ->first();

                if (!$quotation) {
                    abort(404, 'Quotation tidak ditemukan.');
                }

                if (strtolower(trim($quotation->status ?? '')) !== 'approved') {
                    abort(422, 'Quotation harus berstatus approved.');
                }

                $existingOrder = DB::table('sales_orders')
                    ->where('quotation_id', $quotation->quotation_id)
                    ->exists();

                if ($existingOrder) {
                    abort(422, 'Sales Order untuk quotation ini sudah dibuat.');
                }

                $items = DB::table('quotation_items')
                    ->where('quotation_id', $quotation->quotation_id)
                    ->get();

                if ($items->isEmpty()) {
                    abort(422, 'Quotation belum memiliki item produk.');
                }

                do {
                    $number = 'SO-' . now()->format('Ymd') . '-'
                        . strtoupper(Str::random(5));
                } while (
                    DB::table('sales_orders')
                        ->where('sales_order_number', $number)
                        ->exists()
                );

                $salesOrderId = (string) Str::uuid();

                DB::table('sales_orders')->insert([
                    'sales_order_id' => $salesOrderId,
                    'quotation_id' => $quotation->quotation_id,
                    'sales_order_number' => $number,
                    'order_date' => now()->toDateString(),
                    'delivery_date' => null,
                    'subtotal' => $quotation->subtotal,
                    'tax' => $quotation->tax ?? 0,
                    'total_amount' => $quotation->total_amount,
                    'status' => 'confirmed',
                    'notes' => $quotation->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($items as $item) {
                    DB::table('sales_order_items')->insert([
                        'sales_order_item_id' => (string) Str::uuid(),
                        'sales_order_id' => $salesOrderId,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount ?? 0,
                        'subtotal' => $item->subtotal,
                    ]);
                }
            });

            return redirect()
                ->route('sales_orders.index')
                ->with('success', 'Quotation berhasil dikonversi menjadi Sales Order.');
        }

}

