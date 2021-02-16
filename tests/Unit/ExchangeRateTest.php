<?php

namespace Dostrog\Larate\Tests;

use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\Validation;
use InvalidArgumentException;
use Illuminate\Support\Carbon;

class ExchangeRateTest extends TestCase
{
    public const BASE_CURRENCY = 'EUR';
    public const QUOTE_CURRENCY = 'USD';
    public const PROVIDER_NAME = 'cbrf';

    /** @test */
    public function exchange_rate_constructor_access_carbon(): void
    {
        $er = new ExchangeRate(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            1.003,
            Carbon::parse('2021-01-16'),
            self::PROVIDER_NAME
        );

        self::assertSame('EUR/USD', (string) $er->getCurrencyPair());
        self::assertEquals(1.003, $er->getValue());
        self::assertEquals(self::PROVIDER_NAME, $er->getProviderName());

    }

}
