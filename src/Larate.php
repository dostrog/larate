<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use Dostrog\Larate\Contracts\ExchangeRateService as ExchangeRateServiceContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Safe\Exceptions\PcreException;

readonly class Larate implements ExchangeRateProviderContract
{

    public function __construct(
        private ExchangeRateServiceContract $service,
        private CacheInterface              $cache
    ){
    }

    /**
     * @throws BindingResolutionException
     */
    public static function createForBaseCurrency(string $baseCurrency = 'RUB', ?CacheInterface $cache = null): Larate
    {
        $serviceClass = isset(config('larate.service')[$baseCurrency]) && class_exists(config('larate.service')[$baseCurrency])
            ? config('larate.service.' . $baseCurrency)
            : config('larate.service.RUB');

        return new self(new $serviceClass, $cache ?? app()->make('cache.store'));
    }

    public function getProviderName(): string
    {
        return $this->service->getName();
    }

    /**
     * @throws PcreException
     * @throws InvalidArgumentException
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, ?DateTimeInterface $date = null): ExchangeRateContract
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
