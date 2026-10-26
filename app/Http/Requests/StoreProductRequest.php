<?php

namespace App\Http\Requests;

use App\Support\MoneyRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => MoneyRules::amount(),
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'tax_cost' => MoneyRules::amount(),
            'manufacturing_cost' => MoneyRules::amount(),
        ];
    }
}
