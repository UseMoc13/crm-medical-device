<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories.
     */
    public function index(Request $request)
    {
        $query = ProductCategory::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'category_name',
                    'ILIKE',
                    "%{$search}%"
                )

                ->orWhere(
                    'description',
                    'ILIKE',
                    "%{$search}%"
                );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'category_name',
            'created_at',
            'updated_at',
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


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $categories = $query
            ->withCount('products')
            ->paginate(10)
            ->withQueryString();


        return view(
            'product-categories.index',
            compact(
                'categories',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show create category form.
     */
    public function create()
    {
        return view(
            'product-categories.create'
        );
    }


    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'category_name' => [
                'required',
                'string',
                'max:100',
                'unique:product_categories,category_name',
            ],

            'description' => [
                'nullable',
                'string',
            ],

        ]);


        ProductCategory::create(
            $validated
        );


        return redirect()
            ->route('product-categories.index')
            ->with(
                'success',
                'Product category berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified category.
     */
    public function show(
        ProductCategory $productCategory
    )
    {
        $productCategory->load([
            'products' => function ($query) {
                $query
                    ->with('brand')
                    ->orderBy('product_name');
            }
        ]);


        return view(
            'product-categories.show',
            compact(
                'productCategory'
            )
        );
    }


    /**
     * Show edit category form.
     */
    public function edit(
        ProductCategory $productCategory
    )
    {
        return view(
            'product-categories.edit',
            compact(
                'productCategory'
            )
        );
    }


    /**
     * Update the specified category.
     */
    public function update(
        Request $request,
        ProductCategory $productCategory
    )
    {
        $validated = $request->validate([

            'category_name' => [

                'required',

                'string',

                'max:100',

                Rule::unique(
                    'product_categories',
                    'category_name'
                )->ignore(
                    $productCategory->category_id,
                    'category_id'
                ),

            ],

            'description' => [
                'nullable',
                'string',
            ],

        ]);


        $productCategory->update(
            $validated
        );


        return redirect()
            ->route(
                'product-categories.show',
                $productCategory
            )
            ->with(
                'success',
                'Product category berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified category.
     */
    public function destroy(
        ProductCategory $productCategory
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deletion when category still has products
        |--------------------------------------------------------------------------
        */

        if (
            $productCategory
                ->products()
                ->exists()
        ) {

            return redirect()
                ->route(
                    'product-categories.index'
                )
                ->with(
                    'error',
                    'Category tidak dapat dihapus karena masih digunakan oleh product.'
                );
        }


        $productCategory->delete();


        return redirect()
            ->route(
                'product-categories.index'
            )
            ->with(
                'success',
                'Product category berhasil dihapus.'
            );
    }
}