<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Opportunity;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'category',
                'brand',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'product_code',
                    'ILIKE',
                    "%{$search}%"
                )
                    ->orWhere(
                        'product_name',
                        'ILIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'product_type',
                        'ILIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'unit',
                        'ILIKE',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {

            $query->where(
                'category_id',
                $request->category_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brand Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand_id')) {

            $query->where(
                'brand_id',
                $request->brand_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product_type')) {

            $query->where(
                'product_type',
                $request->product_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'product_code',
            'product_name',
            'product_type',
            'unit',
            'price',
            'warranty_period',
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

        $products = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter Data
        |--------------------------------------------------------------------------
        */

        $categories = ProductCategory::query()
            ->orderBy('category_name')
            ->get([
                'category_id',
                'category_name',
            ]);

        $brands = Brand::query()
            ->orderBy('brand_name')
            ->get([
                'brand_id',
                'brand_name',
            ]);

        $productTypes = Product::query()
            ->whereNotNull('product_type')
            ->where(
                'product_type',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy('product_type')
            ->pluck('product_type');

        $statuses = Product::query()
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
            'products.index',
            compact(
                'products',
                'categories',
                'brands',
                'productTypes',
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
        $categories = ProductCategory::query()
            ->orderBy('category_name')
            ->get([
                'category_id',
                'category_name',
            ]);

        $brands = Brand::query()
            ->orderBy('brand_name')
            ->get([
                'brand_id',
                'brand_name',
            ]);

        return view(
            'products.create',
            compact(
                'categories',
                'brands'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'category_id' => [
                'required',
                'uuid',
                'exists:product_categories,category_id',
            ],

            'brand_id' => [
                'required',
                'uuid',
                'exists:brands,brand_id',
            ],

            'product_code' => [
                'required',
                'string',
                'max:100',
                'unique:products,product_code',
            ],

            'product_name' => [
                'required',
                'string',
                'max:255',
            ],

            'product_type' => [
                'required',
                'string',
                'max:100',
            ],

            'specification' => [
                'nullable',
                'string',
            ],

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'warranty_period' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

        ]);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */


    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'opportunityItems.opportunity',
        ]);

        $opportunities = Opportunity::query()
            ->orderByDesc('created_at')
            ->get();

        return view(
            'products.show',
            compact('product', 'opportunities')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Product $product)
    {
        $categories = ProductCategory::query()
            ->orderBy('category_name')
            ->get([
                'category_id',
                'category_name',
            ]);

        $brands = Brand::query()
            ->orderBy('brand_name')
            ->get([
                'brand_id',
                'brand_name',
            ]);

        $product->load([
            'category',
            'brand',
        ]);

        return view(
            'products.edit',
            compact(
                'product',
                'categories',
                'brands'
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
        Product $product
    ) {
        $validated = $request->validate([

            'category_id' => [
                'required',
                'uuid',
                'exists:product_categories,category_id',
            ],

            'brand_id' => [
                'required',
                'uuid',
                'exists:brands,brand_id',
            ],

            'product_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'products',
                    'product_code'
                )->ignore(
                    $product->product_id,
                    'product_id'
                ),
            ],

            'product_name' => [
                'required',
                'string',
                'max:150',
            ],

            'product_type' => [
                'required',
                'string',
                'max:50',
            ],

            'specification' => [
                'nullable',
                'string',
            ],

            'unit' => [
                'required',
                'string',
                'max:20',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'warranty_period' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

        ]);

        $product->update($validated);

        return redirect()
            ->route(
                'products.show',
                $product
            )
            ->with(
                'success',
                'Product berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deletion if product is used by opportunity items
        |--------------------------------------------------------------------------
        */

        if ($product->opportunityItems()->exists()) {

            return redirect()
                ->route(
                    'products.show',
                    $product
                )
                ->with(
                    'error',
                    'Product tidak dapat dihapus karena masih digunakan pada opportunity item.'
                );
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil dihapus.'
            );
    }
}
