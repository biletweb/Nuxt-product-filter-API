<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FilterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/admin/category/create', [CategoryController::class, 'createCategory']);
Route::post('/admin/category/create-subcategory', [CategoryController::class, 'createSubcategory']);
Route::get('/admin/category/get-categories', [CategoryController::class, 'getCategories']);
Route::get('/categories/{slug}/subcategories', [CategoryController::class, 'subcategories']);
Route::get('/categories/{slug}/products/filter', [FilterController::class, 'filter']);

Route::get('/current-user', function (Request $request) {
    $token = $request->bearerToken();

    if (! $token) {
        return response()->json(['error' => 'Access denied. Token not provided.'], 401);
    }

    $response = Http::withToken($token)->get('http://localhost:8001/api/profile');

    if ($response->failed()) {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    return response()->json([
        'user' => $response->json(),
    ]);
});
