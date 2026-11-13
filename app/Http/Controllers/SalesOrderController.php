<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\SalesOrderReportPdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    public function download(SalesOrder $salesOrder)
    {
        $salesOrder->load(['quotation.opportunity.customer', 'items.product']);
        $pdf = \App\Support\SalesOrderPdf::make($salesOrder);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $salesOrder->sales_order_number) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => (string) strlen($pdf),
        ]);
    }

    private function filteredOrders(Request $request)
    {
        $query = SalesOrder::with(['quotation.opportunity.customer'])->orderByDesc('created_at');
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('sales_order_number', 'ILIKE', "%{$search}%")
                    ->orWhere('status', 'ILIKE', "%{$search}%")
                    ->orWhereHas('quotation', fn ($quotation) => $quotation->where('quotation_number', 'ILIKE', "%{$search}%"));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return $query;
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:pdf,csv'],
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);
        $format = $validated['format'];
        $query = $this->filteredOrders($request);
        $filename = 'sales-orders-report-' . now()->format('Ymd-His');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['Sales Order', 'Quotation', 'Customer', 'Order Date', 'Delivery Date', 'Status', 'Subtotal (IDR)', 'Tax (IDR)', 'Discount (IDR)', 'Total (IDR)', 'Notes']);
                foreach ($query->cursor() as $order) {
                    $discount = max(0, round((float) $order->subtotal + (float) $order->tax - (float) $order->total_amount, 2));
                    $fields = [
                        $order->sales_order_number,
                        $order->quotation?->quotation_number ?? '',
                        $order->quotation?->opportunity?->customer?->customer_name ?? '',
                        $order->order_date?->format('Y-m-d') ?? '',
                        $order->delivery_date?->format('Y-m-d') ?? '',
                        $order->status,
                        $order->subtotal,
                        $order->tax ?? 0,
                        $discount,
                        $order->total_amount,
                        $order->notes ?? '',
                    ];
                    // Keep spreadsheet viewers from treating arbitrary database text as formulas.
                    foreach ([0, 1, 2, 5, 10] as $column) {
                        if (is_string($fields[$column]) && preg_match('/^[\x00-\x20]*[=+@\-]/u', $fields[$column])) {
                            $fields[$column] = "'" . $fields[$column];
                        }
                    }
                    fputcsv($out, $fields);
                }
                fclose($out);
            }, $filename . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $orders = $query->limit(2000)->get();
        $pdf = SalesOrderReportPdf::make($orders, [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ]);
    }

    public function index(Request $request)
    {
        $query = $this->filteredOrders($request);

        $orders = $query
            ->paginate(10)
            ->withQueryString();

        return view('sales_orders.index', compact('orders'));
    }

        public function show(string $id)
            {
                $order = SalesOrder::with([
                    'quotation.opportunity.customer',
                    'items.product',
                    'contract',
                ])->findOrFail($id);

                return view('sales_orders.show', compact('order'));
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
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
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
                ->route('quotations.index')
                ->with('success', 'Sales Order berhasil dibuat. Anda dapat membukanya melalui halaman Sales Orders.');
        }

}

