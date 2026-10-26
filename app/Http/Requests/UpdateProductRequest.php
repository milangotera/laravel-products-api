<?php

namespace App\Http\Requests;

use App\Support\MoneyRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'price' => ['sometimes', ...MoneyRules::amount()],
            'currency_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:currencies,id',
            ],
            'tax_cost' => ['sometimes', ...MoneyRules::amount()],
            'manufacturing_cost' => ['sometimes', ...MoneyRules::amount()],
        ];
    }
}
