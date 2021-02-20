<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\Tests\TestCase;
use Illuminate\Support\Carbon;

class ExchangeRateTest extends TestCase
{
    /**
     * @var string
     */
    public const BASE_CURRENCY = 'EUR';
    /**
     * @var string
     */
    public const QUOTE_CURRENCY = 'USD';
    /**
     * @var string
     */
    public const PROVIDER_NAME = 'cbrf';
    /**
     * @var string
     */
    public const DATE = '2020-01-16';

    /** @test */
    public function exchange_rate_constructor_access_carbon(): void
    {
        $er = new ExchangeRate(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            1.003,
            Carbon::parse(self::DATE),
            self::PROVIDER_NAME
        );

        self::assertInstanceOf(Carbon::class, $er->getDate());

        self::assertEquals(self::DATE, $er->getDate()->format('Y-m-d'));
    }

    /** @test */
    public function exchange_rate_constructor_access_right_value(): void
    {
        $er = new ExchangeRate(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            1.003,
            Carbon::parse(self::DATE),
            self::PROVIDER_NAME
        );

        self::assertSame('EUR/USD', (string) $er->getCurrencyPair());
        self::assertEquals(1.003, $er->getValue());
        self::assertEquals(self::PROVIDER_NAME, $er->getProviderName());
    }
}
