<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ProductPriceController extends Controller
{
    public function index(
        Product $product
    ): AnonymousResourceCollection {
        $prices = $product->prices()
            ->with('currency')
            ->orderBy('currency_id')
            ->get();

        return ProductPriceResource::collection($prices);
    }

    public function store(
        StoreProductPriceRequest $request,
        Product $product
    ): JsonResponse {
        $data = $request->validated();

        if ((int) $data['currency_id'] === (int) $product->currency_id) {
            throw ValidationException::withMessages([
                'currency_id' => 'La divisa ya es la moneda base del producto.',
            ]);
        }

        if ($product->prices()
            ->where('currency_id', $data['currency_id'])
            ->exists()) {
            throw ValidationException::withMessages([
                'currency_id' => 'Ya existe un precio para esta divisa.',
            ]);
        }

        $price = $product->prices()->create($data);

        return (new ProductPriceResource(
            $price->load('currency')
        ))->response()->setStatusCode(201);
    }
}
