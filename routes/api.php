<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;

Route::apiResource('products', ProductController::class);

Route::get(
    'products/{product}/prices',
    [ProductPriceController::class, 'index']
);

Route::post(
    'products/{product}/prices',
    [ProductPriceController::class, 'store']
);
