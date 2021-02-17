<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Feature;

use Dostrog\Larate\Facades\LarateFacade;
use Dostrog\Larate\Larate;
use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\CurrencyPair;
use Illuminate\Support\Carbon;
use Dostrog\Larate\CacheHelper;
use Illuminate\Support\Facades\Cache;

class LarateTest extends TestCase
{
    public const BASE_CURRENCY = 'RUB';
    public const QUOTE_CURRENCY = 'USD';
    public const PROVIDER_NAME = 'cbrf';
    public const DATE = '2020-01-16';

    public function test_requesting_the_same_currency(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::BASE_CURRENCY);

        // instantiate from Laravel IoC for inject provider
        $provider = app()->make(Larate::class);

        $rate = $provider->getExchangeRate($pair);

        self::assertEquals(1.0, $rate->getValue());
        self::assertEquals('null', $rate->getProviderName());
    }

    public function test_get_result_from_cache(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
        $date = Carbon::parse(self::DATE);

        $cacheKey = CacheHelper::buildCacheKey($pair, $date);
        Cache::forget($cacheKey);

        // instantiate from Laravel IoC for inject provider
        $provider = app()->make(Larate::class);

        $rate = $provider->getExchangeRate($pair, Carbon::parse(self::DATE));

        self::assertEquals($provider->getProviderName(), $rate->getProviderName());

        $rate = $provider->getExchangeRate($pair, Carbon::parse(self::DATE));
        self::assertEquals('cache', $rate->getProviderName());
    }

    public function test_get_result_using_facade(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
        $date = Carbon::parse(self::DATE);

        $cacheKey = CacheHelper::buildCacheKey($pair, $date);
        Cache::forget($cacheKey);

        $rate = LarateFacade::getExchangeRate($pair, Carbon::parse(self::DATE));

        self::assertEquals(LarateFacade::getProviderName(), $rate->getProviderName());

        $rate = LarateFacade::getExchangeRate($pair, Carbon::parse(self::DATE));
        self::assertEquals('cache', $rate->getProviderName());
    }

    public function test_larate_factory_method(): void
    {
        $provider = Larate::createForBaseCurrency('RUB');
        self::assertEquals('cbrf', $provider->getProviderName());

        $provider = Larate::createForBaseCurrency('UAH');
        self::assertEquals('nbu', $provider->getProviderName());

        $provider = Larate::createForBaseCurrency('foo');
        self::assertEquals('cbrf', $provider->getProviderName());
    }
}
