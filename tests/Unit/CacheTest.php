<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\CacheHelper;
use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheTest extends TestCase
{
    public const BASE_CURRENCY = 'RUB';
    public const QUOTE_CURRENCY = 'EUR';
    public const PROVIDER_NAME = 'cbrf';
    public const DATE = '2020-01-16';

    public function test_cache_helper_build_cache_key(): void
    {
        $expectedKey = sprintf(
            "%s_%s-%s_%s",
            CacheHelper::CACHE_PREFIX,
            self::BASE_CURRENCY,
            self::QUOTE_CURRENCY,
            self::DATE
        );

        self::assertEquals($expectedKey, CacheHelper::buildCacheKey(
            CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY),
            Carbon::parse(self::DATE)
        ));
    }

    public function test_cache_store(): void
    {
        $value = 70.70;
        $pair = CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY);
        $date = Carbon::parse(self::DATE);

        $cacheKey = CacheHelper::buildCacheKey($pair, $date);

        Cache::put($cacheKey, $value);

        self::assertTrue(Cache::has($cacheKey));
        self::assertEquals($value, Cache::get($cacheKey));

        Cache::forget($cacheKey);
        self::assertNull(Cache::get($cacheKey));
    }
}
