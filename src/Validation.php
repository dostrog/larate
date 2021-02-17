<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class Validation
{
    /**
     * Validate that the currency is supported by the
     * Exchange Rates API.
     *
     * @param string $currencyCode
     *
     * @throws InvalidArgumentException
     */
    public static function validateCurrencyCode(string $currencyCode): void
    {
        $currencies = new ISOCurrencies();
        $currencies->contains(new Currency($currencyCode));

        $validator = Validator::make([ 'code' => $currencyCode], [
            'code' => [
                function ($attribute, $value, $fail) use ($currencies) {
                    if (! $currencies->contains(new Currency($value))) {
                        $fail("'{$value}' is not a valid currency code.");
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}
