<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
    Route::apiResource('products', ProductController::class);

    Route::get(
        'products/{product}/prices',
        [ProductPriceController::class, 'index']
    );

    Route::post(
        'products/{product}/prices',
        [ProductPriceController::class, 'store']
    );
});
