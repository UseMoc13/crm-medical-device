<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query();

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

        $categories = $query
            ->orderBy('category_name')
            ->paginate(
                min(
                    max(
                        (int) $request->get('per_page', 10),
                        1
                    ),
                    100
                )
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Product categories retrieved successfully.',
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
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

        $validated['category_id'] = (string) Str::uuid();

        $category = ProductCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(
        ProductCategory $productCategory
    ): JsonResponse {
        $productCategory->load('products');

        return response()->json([
            'success' => true,
            'message' => 'Product category retrieved successfully.',
            'data' => $productCategory,
        ]);
    }

    public function update(
        Request $request,
        ProductCategory $productCategory
    ): JsonResponse {
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

        $productCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product category updated successfully.',
            'data' => $productCategory,
        ]);
    }

    public function destroy(
        ProductCategory $productCategory
    ): JsonResponse {
        $productCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product category deleted successfully.',
        ]);
    }
}
