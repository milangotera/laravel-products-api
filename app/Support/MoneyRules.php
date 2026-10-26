<?php

namespace App\Support;

class MoneyRules
{
    /**
     * @return array<int, string>
     */
    public static function amount(): array
    {
        return [
            'required',
            'numeric',
            'min:0',
            'decimal:0,2',
            'regex:/^[0-9]{1,16}(?:\.[0-9]{1,2})?$/',
        ];
    }
}
