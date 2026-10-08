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

        OpportunityItem::create(
            $validated
        );

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


    /**
     * Show form to edit an opportunity item.
     */
    public function edit(
        Opportunity $opportunity,
        OpportunityItem $item
    ) {
        /*
        |--------------------------------------------------------------------------
        | Make sure item belongs to this opportunity
        |--------------------------------------------------------------------------
        */

        if (
            $item->opportunity_id !==
            $opportunity->opportunity_id
        ) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Get active products
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Include Current Product
        |--------------------------------------------------------------------------
        */

        if (
            $item->product_id &&
            !$products->contains(
                'product_id',
                $item->product_id
            )
        ) {

            $currentProduct = Product::query()
                ->where(
                    'product_id',
                    $item->product_id
                )
                ->first([
                    'product_id',
                    'product_code',
                    'product_name',
                    'product_type',
                    'unit',
                    'price',
                    'status',
                ]);

            if ($currentProduct) {

                $products->prepend(
                    $currentProduct
                );
            }
        }


        return view(
            'opportunities.items.edit',
            compact(
                'opportunity',
                'item',
                'products'
            )
        );
    }


    /**
     * Update an opportunity item.
     */
    public function update(
        Request $request,
        Opportunity $opportunity,
        OpportunityItem $item
    ) {
        /*
        |--------------------------------------------------------------------------
        | Make sure item belongs to this opportunity
        |--------------------------------------------------------------------------
        */

        if (
            $item->opportunity_id !==
            $opportunity->opportunity_id
        ) {
            abort(404);
        }


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


        $item->update(
            $validated
        );


        return redirect()
            ->route(
                'opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                'Opportunity item berhasil diperbarui.'
            );
    }


    /**
     * Delete an opportunity item.
     */
    public function destroy(
        Opportunity $opportunity,
        OpportunityItem $item
    ) {
        /*
        |--------------------------------------------------------------------------
        | Make sure item belongs to this opportunity
        |--------------------------------------------------------------------------
        */

        if (
            $item->opportunity_id !==
            $opportunity->opportunity_id
        ) {
            abort(404);
        }


        $item->delete();


        return redirect()
            ->route(
                'opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                'Opportunity item berhasil dihapus.'
            );
    }
}