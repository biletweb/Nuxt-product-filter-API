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

        return response()->json([
            'categories' => $categories->isEmpty() ? [] : $categories,
        ]);
    }

    public function subcategories(Request $request, $slug)
    {
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = max(1, (int) $request->input('limit', 10));
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            return response()->json([
                'name' => 'Category not found',
            ], 404);
        }

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
            'categoryName' => $category->name,
            'description' => $category->description,
            'ogDescription' => $category->og_description,
            'categoryFilters' => $categoryFilters,
            'products' => $products,
        ]);
    }

    protected function getBreadcrumbs($category)
    {
        $breadcrumbs = [];
        while ($category) {
            $breadcrumbs[] = [
                'name' => $category->name,
                'slug' => $category->slug,
            ];
            $category = $category->parent;
        }

        return array_reverse($breadcrumbs);
    }

    private function getCategoryFilters($category)
    {
        $filters = $category->products()
            ->with('attributes.values.products')
            ->get()
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
        $category->slug = $request->input('slug') ?? Str::slug($request->input('name'));
        $category->description = $request->input('description');
        $category->og_description = $request->input('description');
        $category->save();

        return response()->json([
            'message' => 'Category created successfully.',
        ]);
    }

    public function getCategories()
    {
        $categories = Category::select('id', 'name', 'slug', 'description', 'og_description')->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function createSubcategory(CreateCategoryRequest $request)
    {
        $category = Category::find($request->input('categoryId'));

        if (! $category) {
            return response()->json([
                'name' => 'Category not found',
            ], 404);
        }

        $subcategory = new Category;
        $subcategory->name = $request->input('name');
        $subcategory->slug = $request->input('slug') ?? Str::slug($request->input('name'));
        $subcategory->description = $request->input('description');
        $subcategory->og_description = $request->input('description');
        $category->children()->save($subcategory);

        return response()->json([
            'message' => 'Subcategory created successfully.',
        ]);
    }
}
