<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereNull('parent_id')
            ->select('id', 'name', 'slug', 'description', 'og_description')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'name' => 'Categories not found',
            ], 404);
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
                'name' => 'Category not found',
            ], 404);
        }

        $categoryName = $category->name;
        $breadcrumbs = $this->getBreadcrumbs($category);
        $categories = $category->children()->select('id', 'name', 'slug', 'description', 'og_description')->get();
        $categoryFilters = $this->getCategoryFilters($category);
        $products = $category->products()->with('attributes.values')
            ->select('id', 'name', 'price', 'sale_price')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'breadcrumbs' => $breadcrumbs,
            'categories' => $categories,
            'categoryName' => $categoryName,
            'description' => $category->description,
            'ogDescription' => $category->og_description,
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

    public function createCategory(CreateCategoryRequest $request)
    {
        $category = new Category;
        $category->name = $request->input('name');
        if ($request->input('slug')) {
            $category->slug = $request->input('slug');
        } else {
            $slug = Str::slug($request->input('name'));
            $category->slug = $slug;
        }
        $category->description = $request->input('description');
        $category->og_description = $request->input('description');
        $category->save();

        return response()->json([
            'message' => 'Category created successfully.',
        ]);
    }

    public function getCategories()
    {
        $categories = Category::all();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function getSubcategories(Request $request)
    {
        $category = Category::where('id', $request->input('categoryId'))->first();

        if (! $category) {
            return response()->json([
                'name' => 'Category not found',
            ], 404);
        }

        $subcategories = $category->children()->select('id', 'name')->get();

        return response()->json([
            'subcategories' => $subcategories,
        ]);
    }

    public function createSubcategory(CreateCategoryRequest $request)
    {
        $category = Category::where('id', $request->input('categoryId'))->first();

        if (! $category) {
            return response()->json([
                'name' => 'Category not found',
            ], 404);
        }

        $subcategory = new Category;
        $subcategory->name = $request->input('name');
        if ($request->input('slug')) {
            $subcategory->slug = $request->input('slug');
        } else {
            $slug = Str::slug($request->input('name'));
            $subcategory->slug = $slug;
        }
        $subcategory->description = $request->input('description');
        $category->og_description = $request->input('description');
        $category->children()->save($subcategory);

        return response()->json([
            'message' => 'Subcategory created successfully.',
        ]);
    }
}
