<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\CurrencyPair;
use Illuminate\Support\Carbon;
use Dostrog\Larate\CacheHelper;
use Illuminate\Support\Facades\Cache;

class CacheTest extends TestCase
{
    public const BASE_CURRENCY = 'RUB';
    public const QUOTE_CURRENCY = 'EUR';
    public const PROVIDER_NAME = 'cbrf';
    public const DATE = '2020-01-16';

    public function test_cache_helper_build_cache_key(): void
    {
        $expectedKey = sprintf("%s_%s-%s_%s",
            CacheHelper::CACHE_PREFIX,
            self::BASE_CURRENCY,
            self::QUOTE_CURRENCY,
            self::DATE
        );

        $er = new ExchangeRate(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            70.70,
            Carbon::parse(self::DATE),
            self::PROVIDER_NAME
        );

        self::assertEquals($expectedKey, CacheHelper::buildCacheKey($er));
    }

    public function test_cache_store(): void
    {
        $value = 70.70;

        $er = new ExchangeRate(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            $value,
            Carbon::parse(self::DATE),
            self::PROVIDER_NAME
        );

        $cacheKey = CacheHelper::buildCacheKey($er);

        Cache::put($cacheKey, $er->getValue());

        self::assertTrue(Cache::has($cacheKey));

        self::assertEquals($value, Cache::get($cacheKey));
    }
}
