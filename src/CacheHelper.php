<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Safe\Exceptions\PcreException;
use Safe\Exceptions\StringsException;

final class CacheHelper
{
    public const CACHE_PREFIX = 'larate';

    /**
     * Build the key that can be used for fetching or
     * storing items in the cache.
     *
     * @throws StringsException
     * @throws PcreException
     */
    public static function buildCacheKey(CurrencyPairContract $pair, DateTimeInterface $date): string
    {
        $cacheKey = \Safe\sprintf(
            "%s_%s_%s",
            self::CACHE_PREFIX,
            $pair,
            $date->format('Y-m-d')
        );

        // Replace characters reserved in PSR-16
        $cacheKey = \Safe\preg_replace('#[{}()/\\\@:]#', '-', $cacheKey);

        return $cacheKey;
    }
}
