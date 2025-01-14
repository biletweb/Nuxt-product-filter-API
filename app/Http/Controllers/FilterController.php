<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function filter(Request $request, $slug)
    {
        $filters = $request->input('filters', []);
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = max(1, (int) $request->input('limit', 10));
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            return response()->json([
                'name' => 'Category not found',
            ], 404);
        }

        $query = Product::query()
            ->with('attributes.values')
            ->select('id', 'name', 'price', 'sale_price')
            ->where('category_id', $category->id);

        foreach ($filters as $attribute => $values) {
            if (! is_array($values) || empty($values)) {
                continue;
            }

            $query->whereHas('attributes', function ($query) use ($attribute, $values) {
                $query->where('attributes.name', $attribute)->whereHas('values', function ($query) use ($values) {
                    $query->whereIn('value', $values);
                });
            });
        }

        $filteredProducts = $query->skip($offset)->take($limit)->get();

        if ($filteredProducts->isEmpty()) {
            return response()->json([
                'name' => 'Invalid filters',
            ], 404);
        }

        return response()->json([
            'products' => $filteredProducts,
        ]);
    }
}
