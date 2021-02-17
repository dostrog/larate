<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use RuntimeException;

final class CacheHelper
{
    public const CACHE_PREFIX = 'larate';

    /**
     * Build the key that can be used for fetching or
     * storing items in the cache.
     *
     * @param ExchangeRateContract $rate
     *
     * @return string
     * @throws RuntimeException
     */
    public static function buildCacheKey(ExchangeRateContract $rate): string
    {
        $cacheKey = sprintf("%s_%s_%s",
            self::CACHE_PREFIX,
            $rate->getCurrencyPair(),
            $rate->getDate()->format('Y-m-d')
        );

        // Replace characters reserved in PSR-16
        $cacheKey = preg_replace('#[{}()/\\\@:]#', '-', $cacheKey);

        if (strlen($cacheKey) > 64) {
            throw new RuntimeException("Cache key length exceeds 64 characters ('$cacheKey'). This violates PSR-16 standard");
        }

        return $cacheKey;
    }
}
