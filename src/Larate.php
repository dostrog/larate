<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use Dostrog\Larate\Contracts\ExchangeRateService as ExchangeRateServiceContract;
use Illuminate\Support\Carbon;
use Psr\SimpleCache\CacheInterface;

class Larate implements ExchangeRateProviderContract
{
    /**
     * The service where converted rate are
     */
    private ExchangeRateServiceContract $service;

    /**
     * The cache
     */
    private CacheInterface $cache;

    public static function createForBaseCurrency(string $baseCurrency = 'RUB', CacheInterface $cache = null): Larate
    {
        $serviceClass = isset(config('larate.service')[$baseCurrency]) && class_exists(config('larate.service')[$baseCurrency])
            ? config('larate.service.' . $baseCurrency)
            : config('larate.service.RUB');

        return new self( new $serviceClass,$cache ?? app()->make('cache.store') );
    }

    /**
     * Constructor.
     *
     * @param ExchangeRateServiceContract $service
     * @param CacheInterface $cache
     */
    public function __construct(ExchangeRateServiceContract $service, CacheInterface $cache)
    {
        $this->service = $service;
        $this->cache = $cache;
    }

    public function getProviderName(): string
    {
        return $this->service->getName();
    }

    public function getExchangeRate(CurrencyPairContract $currencyPair, DateTimeInterface $date = null): ExchangeRateContract
    {
        $dateRate = $date ?? Carbon::now();

        if ($currencyPair->isIdentical()) {
            return new ExchangeRate($currencyPair, 1.0, $dateRate, 'null');
        }

        $cacheKey = CacheHelper::buildCacheKey($currencyPair, $dateRate);

        $value = $this->cache->get($cacheKey);

        if ($value !== null) {
            return new ExchangeRate($currencyPair, (float) $value, $dateRate, 'cache');
        }

        $rate = $this->service->getExchangeRate($currencyPair, $dateRate);
        $this->cache->set($cacheKey, $rate->getValue());

        return $rate;
    }
}
