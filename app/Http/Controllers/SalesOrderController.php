<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

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
}
