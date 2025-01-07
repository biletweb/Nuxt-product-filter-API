<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $categories = Category::where('parent_id', null)
            ->select('id', 'name', 'slug')
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'error' => 'Categories not found.',
            ]);
        }

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function subcategories(Request $request, $slug)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            return response()->json([
                'error' => 'Category not found.',
            ]);
        }

        $categoryName = $category->name;
        $breadcrumbs = $this->getBreadcrumbs($category);
        $categories = $category->children()->select('id', 'name', 'slug')
            ->skip($offset)
            ->take($limit)
            ->get();
        $categoryFilters = $this->getCategoryFilters($category);
        $products = $category->products()->with('attributes.values')
            ->select('id', 'name', 'price')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'breadcrumbs' => $breadcrumbs,
            'categories' => $categories,
            'categoryName' => $categoryName,
            'categoryFilters' => $categoryFilters,
            'products' => $products,
        ]);
    }

    protected function getBreadcrumbs($category)
    {
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'name' => $category->name,
            'slug' => $category->slug,
        ];

        while ($category->parent) {
            $category = $category->parent;
            $breadcrumbs[] = [
                'name' => $category->name,
                'slug' => $category->slug,
            ];
        }

        return array_reverse($breadcrumbs);
    }

    private function getCategoryFilters($category)
    {
        $filters = $category->products
            ->flatMap(fn ($product) => $product->attributes)
            ->groupBy('id')
            ->map(fn ($attributes) => [
                'id' => $attributes->first()->id,
                'name' => $attributes->first()->name,
                'values' => $attributes
                    ->flatMap(fn ($attribute) => $attribute->values->filter(
                        fn ($value) => $value->products->where('id', $attribute->pivot->product_id)->isNotEmpty()
                    ))
                    ->unique('id')
                    ->map(fn ($value) => [
                        'id' => $value->id,
                        'value' => $value->value,
                    ])
                    ->values(),
            ])
            ->filter(fn ($filter) => $filter['values']->isNotEmpty())
            ->values();

        return $filters;
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'keywords' => ['required', 'string', 'max:255'],
            'og_description' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $category = new Category;
        $category->name = $request->input('name');
        if ($request->has('slug')) {
            $category->slug = $request->input('slug');
        } else {
            $slug = Str::slug($request->input('name'));
            $category->slug = $slug;
        }
        $category->description = $request->input('description');
        $category->keywords = $request->input('keywords');
        $category->og_description = $request->input('og_description');
        if ($request->has('parent_id')) {
            $category->parent_id = $request->input('parent_id');
        }
        $category->save();

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category->only(['id', 'name', 'slug', 'parent_id']),
        ]);
    }
}
