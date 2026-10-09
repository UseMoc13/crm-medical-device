<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Quotation::query()
            ->with([
                'opportunity',
            ]);

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'quotation_number',
                    'ILIKE',
                    "%{$search}%"
                )

                    ->orWhere(
                        'status',
                        'ILIKE',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('opportunity_id')) {

            $query->where(
                'opportunity_id',
                $request->opportunity_id
            );
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        $allowedSorts = [
            'quotation_number',
            'quotation_date',
            'valid_until',
            'subtotal',
            'total_amount',
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

        if (
            !in_array(
                $sort,
                $allowedSorts
            )
        ) {

            $sort = 'created_at';
        }

        if (
            !in_array(
                $direction,
                ['asc', 'desc']
            )
        ) {

            $direction = 'desc';
        }

        $query->orderBy(
            $sort,
            $direction
        );

        $quotations = $query
            ->paginate(10)
            ->withQueryString();

        $opportunities = Opportunity::query()
            ->orderBy('name')
            ->get([
                'opportunity_id',
                'opportunity_code',
                'name',
            ]);

        $statuses = Quotation::query()
            ->whereNotNull('status')
            ->where(
                'status',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view(
            'quotations.index',
            compact(
                'quotations',
                'opportunities',
                'statuses',
                'sort',
                'direction'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $opportunities = Opportunity::query()
            ->with([
                'customer',
                'user',
                'items.product',
            ])
            ->orderBy('name')
            ->get([
                'opportunity_id',
                'opportunity_code',
                'name',
                'customer_id',
                'user_id',
                'stage',
                'status',
            ]);

        /*
    |--------------------------------------------------------------------------
    | Prepare Opportunity Data For Create Quotation
    |--------------------------------------------------------------------------
    */

        $opportunityData = [];

        foreach ($opportunities as $opportunity) {

            $customerName = 'No Customer';

            if ($opportunity->customer) {

                $customerName =
                    $opportunity->customer->customer_name
                    ?? $opportunity->customer->company_name
                    ?? $opportunity->customer->name
                    ?? 'No Customer';
            }


            $salesName = 'No Sales';

            if ($opportunity->user) {

                $salesName =
                    $opportunity->user->name
                    ?? 'No Sales';
            }


            $items = [];


            foreach ($opportunity->items as $item) {

                $price =
                    $item->estimated_price;


                /*
            |--------------------------------------------------------------------------
            | Fallback To Product Price
            |--------------------------------------------------------------------------
            */

                if (
                    $price === null &&
                    $item->product
                ) {

                    $price =
                        $item->product->price;
                }


                $items[] = [

                    'opportunity_item_id' =>
                        $item->opportunity_item_id,

                    'product_id' =>
                        $item->product_id,

                    'product_code' =>
                        $item->product
                        ? $item->product->product_code
                        : '-',

                    'product_name' =>
                        $item->product
                        ? $item->product->product_name
                        : 'Unknown Product',

                    'quantity' =>
                        (int) (
                            $item->quantity ?? 1
                        ),

                    'unit' =>
                        $item->product
                        ? ($item->product->unit ?? 'Unit')
                        : 'Unit',

                    'estimated_price' =>
                        $price !== null
                        ? (float) $price
                        : 0,

                ];
            }


            $opportunityData[$opportunity->opportunity_id] = [

                'id' =>
                    $opportunity->opportunity_id,

                'code' =>
                    $opportunity->opportunity_code,

                'name' =>
                    $opportunity->name,

                'customer' =>
                    $customerName,

                'sales' =>
                    $salesName,

                'stage' =>
                    $opportunity->stage,

                'status' =>
                    $opportunity->status,

                'items' =>
                    $items,

            ];
        }


        return view(
            'quotations.create',
            compact(
                'opportunities',
                'opportunityData'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'opportunity_id' => [
                'required',
                'uuid',
                'exists:opportunities,opportunity_id',
            ],

            'quotation_date' => [
                'required',
                'date',
            ],

            'valid_until' => [
                'nullable',
                'date',
                'after_or_equal:quotation_date',
            ],

            'discount_enabled' => [
                'nullable',
                'boolean',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'discount_type' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'amount',
                ]),
            ],

            'tax_enabled' => [
                'nullable',
                'boolean',
            ],

            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax_type' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'amount',
                ]),
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Opportunity Items
            |--------------------------------------------------------------------------
            */

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.opportunity_item_id' => [
                'required',
                'uuid',
            ],

            'items.*.product_id' => [
                'required',
                'uuid',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        $discountEnabled =
            $request->boolean(
                'discount_enabled'
            );

        $discountType =
            $validated['discount_type']
            ?? 'amount';

        $discount =
            $validated['discount']
            ?? 0;

        if (!$discountEnabled) {

            $discount = 0;

            $discountType = 'amount';
        }


        if (
            $discountEnabled &&
            $discountType === 'percentage' &&
            $discount > 100
        ) {

            return back()
                ->withErrors([
                    'discount' =>
                        'Discount dalam persen tidak boleh lebih dari 100%.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Tax
        |--------------------------------------------------------------------------
        */

        $taxEnabled =
            $request->boolean(
                'tax_enabled'
            );

        $taxType =
            $validated['tax_type']
            ?? 'amount';

        $tax =
            $validated['tax']
            ?? 0;

        if (!$taxEnabled) {

            $tax = 0;

            $taxType = 'amount';
        }


        if (
            $taxEnabled &&
            $taxType === 'percentage' &&
            $tax > 100
        ) {

            return back()
                ->withErrors([
                    'tax' =>
                        'Tax dalam persen tidak boleh lebih dari 100%.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Get Opportunity
        |--------------------------------------------------------------------------
        */

        $opportunity = Opportunity::query()
            ->with([
                'items.product',
            ])
            ->findOrFail(
                $validated['opportunity_id']
            );


        /*
        |--------------------------------------------------------------------------
        | Build Items
        |--------------------------------------------------------------------------
        */

        $requestItems =
            $validated['items'] ?? [];

        $quotationItems = [];

        $subtotal = 0;


        foreach (
            $requestItems as $item
        ) {

            /*
            |--------------------------------------------------------------------------
            | Find Opportunity Item
            |--------------------------------------------------------------------------
            */

            $opportunityItem =
                $opportunity->items
                    ->firstWhere(
                        'opportunity_item_id',
                        $item['opportunity_item_id']
                    );


            if (!$opportunityItem) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Security Check
            |--------------------------------------------------------------------------
            |
            | Product harus berasal dari Opportunity Item.
            |
            */

            if (
                $opportunityItem->product_id
                !== $item['product_id']
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */

            $quantity =
                (int) $item['quantity'];


            /*
            |--------------------------------------------------------------------------
            | Unit Price
            |--------------------------------------------------------------------------
            |
            | Primary:
            | opportunity_items.estimated_price
            |
            | Fallback:
            | products.price
            |
            */

            $unitPrice =
                $opportunityItem->estimated_price;

            if (
                $unitPrice === null &&
                $opportunityItem->product
            ) {

                $unitPrice =
                    $opportunityItem->product->price;
            }


            $unitPrice =
                (float) (
                    $unitPrice ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | Item Subtotal
            |--------------------------------------------------------------------------
            */

            $itemSubtotal =
                $quantity * $unitPrice;


            $subtotal +=
                $itemSubtotal;


            /*
            |--------------------------------------------------------------------------
            | Store temporary item data
            |--------------------------------------------------------------------------
            */

            $quotationItems[] = [

                'product_id' =>
                    $opportunityItem->product_id,

                'quantity' =>
                    $quantity,

                'unit_price' =>
                    $unitPrice,

                'discount' =>
                    0,

                'subtotal' =>
                    $itemSubtotal,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Discount Amount
        |--------------------------------------------------------------------------
        */

        $discountAmount = 0;


        if ($discountEnabled) {

            if (
                $discountType === 'percentage'
            ) {

                $discountAmount =
                    $subtotal *
                    ($discount / 100);
            } else {

                $discountAmount =
                    $discount;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Negative Subtotal
        |--------------------------------------------------------------------------
        */

        $discountAmount =
            min(
                $discountAmount,
                $subtotal
            );


        $afterDiscount =
            max(
                0,
                $subtotal -
                $discountAmount
            );


        /*
        |--------------------------------------------------------------------------
        | Tax Amount
        |--------------------------------------------------------------------------
        */

        $taxAmount = 0;


        if ($taxEnabled) {

            if (
                $taxType === 'percentage'
            ) {

                $taxAmount =
                    $afterDiscount *
                    ($tax / 100);
            } else {

                $taxAmount =
                    $tax;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $totalAmount =
            $afterDiscount +
            $taxAmount;


        /*
        |--------------------------------------------------------------------------
        | Generate Number
        |--------------------------------------------------------------------------
        */

        $quotationNumber =
            $this->generateQuotationNumber();


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use ($validated, $quotationNumber, $subtotal, $discount, $discountType, $tax, $taxType, $totalAmount, $quotationItems) {

                $quotation =
                    Quotation::create([

                        'quotation_id' =>
                            (string) Str::uuid(),

                        'opportunity_id' =>
                            $validated['opportunity_id'],

                        'quotation_number' =>
                            $quotationNumber,

                        'quotation_date' =>
                            $validated['quotation_date'],

                        'valid_until' =>
                            $validated['valid_until']
                            ?? null,

                        'subtotal' =>
                            $subtotal,

                        'discount' =>
                            $discount,

                        'discount_type' =>
                            $discountType,

                        'tax' =>
                            $tax,

                        'tax_type' =>
                            $taxType,

                        'total_amount' =>
                            $totalAmount,

                        'status' =>
                            $validated['status'],

                        'notes' =>
                            $validated['notes']
                            ?? null,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Create Quotation Items
                |--------------------------------------------------------------------------
                */

                foreach (
                    $quotationItems
                    as $item
                ) {

                    QuotationItem::create([

                        'quotation_id' =>
                            $quotation->quotation_id,

                        'product_id' =>
                            $item['product_id'],

                        'quantity' =>
                            $item['quantity'],

                        'unit_price' =>
                            $item['unit_price'],

                        'discount' =>
                            $item['discount'],

                        'subtotal' =>
                            $item['subtotal'],

                    ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'quotations.index'
            )
            ->with(
                'success',
                'Quotation berhasil ditambahkan.'
            );
    }


    /*
/*
|--------------------------------------------------------------------------
| SHOW
|--------------------------------------------------------------------------
*/

    public function show(
        Quotation $quotation
    ) {

        $quotation->load([

            'opportunity.customer',

            'opportunity.user',

            'opportunity.lead',

        ]);

        $quotationItems = $quotation
            ->items()
            ->with('product')
            ->orderBy('quotation_item_id', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'quotations.show',
            compact(
                'quotation',
                'quotationItems'
            )
        );
    }

    /*
|--------------------------------------------------------------------------
| QUOTATION ITEMS
|--------------------------------------------------------------------------
*/

    public function items(
        Request $request,
        Quotation $quotation
    ) {

        $query = $quotation
            ->items()
            ->with('product');


        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->whereHas(
                'product',
                function ($q) use ($search) {

                    $q->where(
                        'product_name',
                        'ILIKE',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'product_code',
                            'ILIKE',
                            "%{$search}%"
                        );
                }
            );
        }


        $sort = $request->get(
            'sort',
            'created_at'
        );


        $direction = $request->get(
            'direction',
            'asc'
        );


        $allowedSorts = [

            'created_at',
            'quantity',
            'unit_price',
            'subtotal',

        ];


        if (
            !in_array(
                $sort,
                $allowedSorts,
                true
            )
        ) {

            $sort = 'created_at';
        }


        if (
            !in_array(
                $direction,
                ['asc', 'desc'],
                true
            )
        ) {

            $direction = 'asc';
        }


        $query->orderBy(
            $sort,
            $direction
        );


        $items = $query
            ->paginate(20)
            ->withQueryString();


        return response()->json([

            'data' => $items->map(
                function ($item) {

                    return [

                        'id' =>
                            $item->quotation_item_id,

                        'product_name' =>
                            $item->product
                            ? $item->product->product_name
                            : 'Unknown Product',

                        'product_code' =>
                            $item->product
                            ? $item->product->product_code
                            : '-',

                        'quantity' =>
                            (int) $item->quantity,

                        'unit_price' =>
                            (float) $item->unit_price,

                        'discount' =>
                            (float) ($item->discount ?? 0),

                        'subtotal' =>
                            (float) $item->subtotal,

                    ];
                }
            )->values(),

            'current_page' =>
                $items->currentPage(),

            'last_page' =>
                $items->lastPage(),

            'per_page' =>
                $items->perPage(),

            'total' =>
                $items->total(),

            'from' =>
                $items->firstItem(),

            'to' =>
                $items->lastItem(),

        ]);
    }

    public function edit(
        Quotation $quotation
    ) {

        $opportunities =
            Opportunity::query()
                ->orderBy('name')
                ->get([
                    'opportunity_id',
                    'opportunity_code',
                    'name',
                    'customer_id',
                    'stage',
                    'status',
                ]);

        return view(
            'quotations.edit',
            compact(
                'quotation',
                'opportunities'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Quotation $quotation
    ) {

        $validated = $request->validate([

            'opportunity_id' => [
                'required',
                'uuid',
                'exists:opportunities,opportunity_id',
            ],

            'quotation_date' => [
                'required',
                'date',
            ],

            'valid_until' => [
                'nullable',
                'date',
                'after_or_equal:quotation_date',
            ],

            'discount_enabled' => [
                'nullable',
                'boolean',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'discount_type' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'amount',
                ]),
            ],

            'tax_enabled' => [
                'nullable',
                'boolean',
            ],

            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax_type' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'amount',
                ]),
            ],

            'status' => [
                'required',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        $discountEnabled =
            $request->boolean(
                'discount_enabled'
            );

        $discountType =
            $validated['discount_type']
            ?? 'amount';

        $discount =
            $validated['discount']
            ?? 0;


        if (!$discountEnabled) {

            $discount = 0;

            $discountType = 'amount';
        }


        if (
            $discountEnabled &&
            $discountType === 'percentage' &&
            $discount > 100
        ) {

            return back()
                ->withErrors([
                    'discount' =>
                        'Discount dalam persen tidak boleh lebih dari 100%.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Tax
        |--------------------------------------------------------------------------
        */

        $taxEnabled =
            $request->boolean(
                'tax_enabled'
            );

        $taxType =
            $validated['tax_type']
            ?? 'amount';

        $tax =
            $validated['tax']
            ?? 0;


        if (!$taxEnabled) {

            $tax = 0;

            $taxType = 'amount';
        }


        if (
            $taxEnabled &&
            $taxType === 'percentage' &&
            $tax > 100
        ) {

            return back()
                ->withErrors([
                    'tax' =>
                        'Tax dalam persen tidak boleh lebih dari 100%.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Subtotal
        |--------------------------------------------------------------------------
        */

        $subtotal =
            (float) $quotation->subtotal;


        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        $discountAmount = 0;


        if ($discountEnabled) {

            if (
                $discountType === 'percentage'
            ) {

                $discountAmount =
                    $subtotal *
                    ($discount / 100);
            } else {

                $discountAmount =
                    $discount;
            }
        }


        $discountAmount =
            min(
                $discountAmount,
                $subtotal
            );


        $afterDiscount =
            max(
                0,
                $subtotal -
                $discountAmount
            );


        /*
        |--------------------------------------------------------------------------
        | Tax
        |--------------------------------------------------------------------------
        */

        $taxAmount = 0;


        if ($taxEnabled) {

            if (
                $taxType === 'percentage'
            ) {

                $taxAmount =
                    $afterDiscount *
                    ($tax / 100);
            } else {

                $taxAmount =
                    $tax;
            }
        }


        $totalAmount =
            $afterDiscount +
            $taxAmount;


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $validated['discount'] =
            $discount;

        $validated['discount_type'] =
            $discountType;

        $validated['tax'] =
            $tax;

        $validated['tax_type'] =
            $taxType;

        $validated['subtotal'] =
            $subtotal;

        $validated['total_amount'] =
            $totalAmount;


        $quotation->update(
            $validated
        );


        return redirect()
            ->route(
                'quotations.show',
                $quotation
            )
            ->with(
                'success',
                'Quotation berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Quotation $quotation
    ) {

        if (
            $quotation->items()->exists()
        ) {

            return redirect()
                ->route(
                    'quotations.index'
                )
                ->with(
                    'error',
                    'Quotation tidak dapat dihapus karena masih memiliki item.'
                );
        }


        $quotation->delete();


        return redirect()
            ->route(
                'quotations.index'
            )
            ->with(
                'success',
                'Quotation berhasil dihapus.'
            );



        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE QUOTATION NUMBER
    |--------------------------------------------------------------------------
    */

    private function generateQuotationNumber()
    {

        do {

            $number =
                'QUO-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(5)
                );
        } while (
            Quotation::where(
                'quotation_number',
                $number
            )->exists()
        );


        return $number;
    }
}
