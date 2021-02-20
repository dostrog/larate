<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class Validation
{
    /**
     * Validate that the currency is supported by the
     * Exchange Rates API.
     *
     * @throws InvalidArgumentException
     */
    public static function validateCurrencyCode(string $currencyCode): void
    {
        $currencies = new ISOCurrencies();
        $currencies->contains(new Currency($currencyCode));

        $validator = Validator::make([ 'code' => $currencyCode], [
            'code' => [
                function ($attribute, $value, $fail) use ($currencies): void {
                    if (! $currencies->contains(new Currency($value))) {
                        $fail( trans('larate::validation.code', ['code' => $value]) );
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}
