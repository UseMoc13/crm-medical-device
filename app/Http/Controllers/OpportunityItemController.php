<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\OpportunityItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OpportunityItemController extends Controller
{
    /**
     * Show form to create a new opportunity item.
     */
    public function create(Opportunity $opportunity)
    {
        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('product_name')
            ->get([
                'product_id',
                'product_code',
                'product_name',
                'product_type',
                'unit',
                'price',
                'status',
            ]);

        return view(
            'opportunities.items.create',
            compact(
                'opportunity',
                'products'
            )
        );
    }

    /**
     * Store a new opportunity item.
     */
    public function store(
        Request $request,
        Opportunity $opportunity
    ) {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'uuid',
                'exists:products,product_id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'estimated_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['opportunity_id'] =
            $opportunity->opportunity_id;

        OpportunityItem::create($validated);

        return redirect()
            ->route(
                'opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                'Opportunity item berhasil ditambahkan.'
            );
    }
}