<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;

final class CacheHelper
{
    public const CACHE_PREFIX = 'larate_rate_';

    /**
     * Build the key that can be used for fetching or
     * storing items in the cache.
     *
     * @param ExchangeRateContract $rate
     *
     * @return string
     */
    public static function buildCacheKey(ExchangeRateContract $rate): string
    {
        return self::CACHE_PREFIX . (string) $rate->getCurrencyPair() . '_' . $rate->getDate()->format('Y-m-d');
    }
}
