<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $allowedSorts = [
            'brand_name',
            'created_at',
        ];

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        $brands = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'brands.index',
            compact(
                'brands',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new brand.
     */
    public function create()
    {
        return view('brands.create');
    }


    /**
     * Store a newly created brand.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => [
                'required',
                'string',
                'max:255',
                'unique:brands,brand_name',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Brand::create([
            'brand_id' => (string) Str::uuid(),
            'brand_name' => $validated['brand_name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }


    /**
     * Display the specified brand.
     */
public function show(Brand $brand)
{
    $products = $brand->products()
        ->latest('created_at')
        ->paginate(10)
        ->withQueryString();

    return view(
        'brands.show',
        compact(
            'brand',
            'products'
        )
    );
}


    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand)
    {
        return view(
            'brands.edit',
            compact('brand')
        );
    }


    /**
     * Update the specified brand.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'brand_name' => [
                'required',
                'string',
                'max:255',
                'unique:brands,brand_name,' . $brand->brand_id . ',brand_id',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $brand->update([
            'brand_name' => $validated['brand_name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('brands.show', $brand)
            ->with('success', 'Brand updated successfully.');
    }


    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand)
    {
        /*
         * Prevent deleting a brand that is still used
         * by existing products.
         */
        if ($brand->products()->exists()) {
            return redirect()
                ->route('brands.index')
                ->with(
                    'error',
                    'This brand cannot be deleted because it is still being used by one or more products.'
                );
        }

        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}

