<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::with('currency')
            ->orderByDesc('id')
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource(
            $product->load('currency')
        ))->response()->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource(
            $product->load(['currency', 'prices.currency'])
        );
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): ProductResource {
        $data = $request->validated();

        $product = DB::transaction(function () use ($product, $data): Product {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $changesCurrency = array_key_exists('currency_id', $data)
                && (int) $data['currency_id'] !== (int) $lockedProduct->currency_id;

            if ($changesCurrency) {
                foreach (['price', 'tax_cost', 'manufacturing_cost'] as $field) {
                    if (! array_key_exists($field, $data)) {
                        throw ValidationException::withMessages([
                            $field => 'Debes enviar este importe al cambiar la moneda base.',
                        ]);
                    }
                }

                if ($lockedProduct->prices()
                    ->where('currency_id', $data['currency_id'])
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'currency_id' => 'Ya existe un precio adicional en esta divisa.',
                    ]);
                }
            }

            $lockedProduct->update($data);

            return $lockedProduct;
        });

        return new ProductResource(
            $product->load(['currency', 'prices.currency'])
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
