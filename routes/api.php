<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FilterController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/category/create', [CategoryController::class, 'createCategory']);
Route::get('/categories/{slug}/subcategories', [CategoryController::class, 'subcategories']);
Route::get('/products/{id}/subcategories/filter', [FilterController::class, 'filter']);
