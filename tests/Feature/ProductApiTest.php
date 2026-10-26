<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private Currency $usd;

    private Currency $eur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usd = Currency::create([
            'name' => 'USD',
            'symbol' => '$',
            'exchange_rate' => '1.00000000',
        ]);

        $this->eur = Currency::create([
            'name' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => '0.85000000',
        ]);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    private function payload(): array
    {
        return [
            'name' => 'Teclado',
            'description' => 'Teclado para computadora',
            'price' => '25.50',
            'currency_id' => $this->usd->id,
            'tax_cost' => '2.00',
            'manufacturing_cost' => '10.00',
        ];
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();

        $this->postJson('/api/products', $this->payload())
            ->assertUnauthorized();
    }

    public function test_product_can_be_created_listed_read_updated_and_deleted(): void
    {
        $this->authenticate();

        $response = $this->postJson('/api/products', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Teclado');

        $id = $response->json('data.id');

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/products/{$id}")
            ->assertOk()
            ->assertJsonPath('data.price', '25.50');

        $this->putJson("/api/products/{$id}", ['name' => 'Teclado actualizado'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Teclado actualizado');

        $this->deleteJson("/api/products/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $id]);

        $this->getJson("/api/products/{$id}")->assertNotFound();
    }

    public function test_invalid_product_data_is_rejected(): void
    {
        $this->authenticate();

        $this->postJson('/api/products', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'description',
                'price',
                'currency_id',
                'tax_cost',
                'manufacturing_cost',
            ]);

        foreach (['-1', '10.123', '10000000000000000.00'] as $amount) {
            $this->postJson('/api/products', [
                ...$this->payload(),
                'price' => $amount,
                'tax_cost' => $amount,
                'manufacturing_cost' => $amount,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'price',
                    'tax_cost',
                    'manufacturing_cost',
                ]);
        }

        $this->postJson('/api/products', [
            ...$this->payload(),
            'currency_id' => 999999,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_prices_can_be_registered_and_listed_without_duplicates(): void
    {
        $this->authenticate();

        $product = Product::create($this->payload());
        $url = "/api/products/{$product->id}/prices";

        $this->postJson($url, [
            'currency_id' => $this->eur->id,
            'price' => '22.00',
        ])->assertCreated();

        $this->getJson($url)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.price', '22.00');

        $this->postJson($url, [
            'currency_id' => $this->eur->id,
            'price' => '23.00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');

        $this->postJson($url, [
            'currency_id' => $this->usd->id,
            'price' => '25.00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_base_currency_cannot_conflict_with_an_additional_price(): void
    {
        $this->authenticate();

        $product = Product::create($this->payload());

        $product->prices()->create([
            'currency_id' => $this->eur->id,
            'price' => '22.00',
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'currency_id' => $this->eur->id,
            'price' => '22.00',
            'tax_cost' => '1.00',
            'manufacturing_cost' => '8.00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_changing_currency_requires_all_monetary_values(): void
    {
        $this->authenticate();

        $product = Product::create($this->payload());

        $this->putJson("/api/products/{$product->id}", [
            'currency_id' => $this->eur->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price');

        $this->putJson("/api/products/{$product->id}", [
            'currency_id' => $this->eur->id,
            'price' => '22.00',
            'tax_cost' => '1.00',
            'manufacturing_cost' => '8.00',
        ])
            ->assertOk()
            ->assertJsonPath('data.currency_id', $this->eur->id);
    }

    public function test_deleting_product_also_deletes_its_prices(): void
    {
        $this->authenticate();

        $product = Product::create($this->payload());

        $price = $product->prices()->create([
            'currency_id' => $this->eur->id,
            'price' => '22.00',
        ]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('product_prices', ['id' => $price->id]);
    }
}
