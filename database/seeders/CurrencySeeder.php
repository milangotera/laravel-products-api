<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'USD',
                'symbol' => '$',
                'exchange_rate' => '1.00000000',
            ],
            [
                'name' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => '0.85000000',
            ],
            [
                'name' => 'VES',
                'symbol' => 'Bs.',
                'exchange_rate' => '100.00000000',
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['name' => $currency['name']],
                $currency
            );
        }
    }
}
