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
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            return response()->json([
                'error' => 'Category not found.',
            ]);
        }

        $query = Product::query()
            ->with('attributes.values')
            ->select('id', 'name', 'price', 'sale_price')
            ->where('category_id', $category->id);

        foreach ($filters as $attribute => $valueIds) {
            if (empty($valueIds)) {
                continue;
            }
            $query->whereHas('attributes', function ($query) use ($attribute, $valueIds) {
                $query->where('attribute_id', $attribute);
                if (is_array($valueIds)) {
                    $query->whereIn('value_id', $valueIds);
                } else {
                    $query->where('value_id', $valueIds);
                }
            });
        }

        $filteredProducts = $query->skip($offset)->take($limit)->get();

        return response()->json([
            'products' => $filteredProducts,
        ]);
    }
}
