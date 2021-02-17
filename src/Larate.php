<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Cache;
use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateService as ExchangeRateServiceContract;
use Dostrog\Larate\Contracts\ExchangeRateProvider as ExchangeRateProviderContract;
use Dostrog\Larate\Services\NationalBankOfUkraine;
use Illuminate\Support\Carbon;

class Larate implements ExchangeRateProviderContract
{
    /**
     * The service.
     */
    private ExchangeRateServiceContract $service;

    public static function createForBaseCurrency(string $baseCurrency = 'RUB'): Larate
    {
        if ( isset( config('larate.service')[$baseCurrency])
            && class_exists(config('larate.service')[$baseCurrency])) {

            $serviceClass = config('larate.service.' . $baseCurrency);
            return new self( new $serviceClass );
        }

        $serviceClass = config('larate.service.RUB');
        return new self( new $serviceClass );
    }

    /**
     * Constructor.
     *
     * @param ExchangeRateServiceContract $service
     */

    public function __construct(ExchangeRateServiceContract $service)
    {
        $this->service = $service;
    }

    public function getProviderName(): string
    {
        return $this->service->getName();
    }

    public function getExchangeRate(CurrencyPairContract $currencyPair, DateTimeInterface $date = null): ExchangeRateContract
    {
        if ($currencyPair->isIdentical()) {
            return new ExchangeRate($currencyPair, 1.0, Carbon::now(), 'null');
        }

        $cacheKey = CacheHelper::buildCacheKey($currencyPair, $date ?? Carbon::now());

        $value = Cache::get($cacheKey);

        if ($value !== null) {
            return new ExchangeRate($currencyPair, (float) $value, $date, 'cache');
        }

        $rate = $this->service->getExchangeRate($currencyPair, $date);
        Cache::put($cacheKey, $rate->getValue());

        return $rate;
    }
}
