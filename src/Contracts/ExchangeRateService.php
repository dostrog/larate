<?php
declare(strict_types=1);

namespace Dostrog\Larate\Contracts;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;

interface ExchangeRateService
{
    /**
     * Gets the name of the exchange rate service.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the exchange rate.
     *
     * @param CurrencyPair           $currencyPair Request rate for pair in format 'RUB/USD'
     * @param DateTimeInterface|null $date         If null get latest known rate (or for now if provider does not return latest)
     * @return ExchangeRateContract
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, DateTimeInterface $date = null): ExchangeRateContract;
}
