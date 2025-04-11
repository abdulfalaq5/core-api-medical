<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Routes Group
Route::middleware(['web'])->prefix('api')->group(function () {
    // Protected routes
    /**
     * php artisan route:list
     * php artisan route:list --path=api
     * untuk melihat route yang ada
     */
    Route::middleware('api.key')->group(function () {
        Route::apiResource('categories', CategoriesController::class);
        Route::apiResource('products', ProductsController::class);
    });
});

