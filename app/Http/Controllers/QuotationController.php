<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
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

        if (!in_array(
            $sort,
            $allowedSorts
        )) {
            $sort = 'created_at';
        }

        if (!in_array(
            $direction,
            ['asc', 'desc']
        )) {
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

    public function create()
    {
        $opportunities = Opportunity::query()
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
            'quotations.create',
            compact(
                'opportunities'
            )
        );
    }

    public function store(
        Request $request
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
                'required',
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
                'required',
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
        ]);

        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        $discountEnabled =
            $request->boolean('discount_enabled');

        $discountType =
            $validated['discount_type'];

        $discount =
            $validated['discount'] ?? 0;

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
            $request->boolean('tax_enabled');

        $taxType =
            $validated['tax_type'];

        $tax =
            $validated['tax'] ?? 0;

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
        | Quotation Values
        |--------------------------------------------------------------------------
        */

        $validated['quotation_number'] =
            $this->generateQuotationNumber();

        /*
        |--------------------------------------------------------------------------
        | Subtotal & Total
        |--------------------------------------------------------------------------
        |
        | Untuk sekarang quotation item belum menjadi sumber perhitungan.
        | Setelah Quotation Items selesai, bagian ini akan dihitung otomatis.
        |
        */

        $subtotal = 0;

        $discountAmount = 0;

        $taxAmount = 0;

        if ($discountEnabled) {
            if ($discountType === 'percentage') {
                $discountAmount =
                    $subtotal * ($discount / 100);
            } else {
                $discountAmount =
                    $discount;
            }
        }

        $afterDiscount =
            max(
                0,
                $subtotal - $discountAmount
            );

        if ($taxEnabled) {
            if ($taxType === 'percentage') {
                $taxAmount =
                    $afterDiscount * ($tax / 100);
            } else {
                $taxAmount =
                    $tax;
            }
        }

        $totalAmount =
            $afterDiscount + $taxAmount;

        $validated['subtotal'] =
            $subtotal;

        $validated['discount'] =
            $discount;

        $validated['discount_type'] =
            $discountType;

        $validated['tax'] =
            $tax;

        $validated['tax_type'] =
            $taxType;

        $validated['total_amount'] =
            $totalAmount;

        Quotation::create(
            $validated
        );

        return redirect()
            ->route(
                'quotations.index'
            )
            ->with(
                'success',
                'Quotation berhasil ditambahkan.'
            );
    }

    public function show(
        Quotation $quotation
    ) {
        $quotation->load([
            'opportunity.customer',
            'opportunity.user',
            'opportunity.lead',
            'items.product',
        ]);

        return view(
            'quotations.show',
            compact(
                'quotation'
            )
        );
    }

    public function edit(
        Quotation $quotation
    ) {
        $opportunities = Opportunity::query()
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
                'required',
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
                'required',
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
            $request->boolean('discount_enabled');

        $discountType =
            $validated['discount_type'];

        $discount =
            $validated['discount'] ?? 0;

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
            $request->boolean('tax_enabled');

        $taxType =
            $validated['tax_type'];

        $tax =
            $validated['tax'] ?? 0;

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
        | Recalculate
        |--------------------------------------------------------------------------
        |
        | Saat ini subtotal berasal dari quotation.
        | Nanti akan diganti/dihitung ulang dari quotation_items.
        |
        */

        $subtotal =
            (float) $quotation->subtotal;

        $discountAmount = 0;

        $taxAmount = 0;

        if ($discountEnabled) {
            if ($discountType === 'percentage') {
                $discountAmount =
                    $subtotal * ($discount / 100);
            } else {
                $discountAmount =
                    $discount;
            }
        }

        $afterDiscount =
            max(
                0,
                $subtotal - $discountAmount
            );

        if ($taxEnabled) {
            if ($taxType === 'percentage') {
                $taxAmount =
                    $afterDiscount * ($tax / 100);
            } else {
                $taxAmount =
                    $tax;
            }
        }

        $totalAmount =
            $afterDiscount + $taxAmount;

        $validated['discount'] =
            $discount;

        $validated['discount_type'] =
            $discountType;

        $validated['tax'] =
            $tax;

        $validated['tax_type'] =
            $taxType;

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
    }

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