<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;

final readonly class ExchangeRate implements ExchangeRateContract
{
    /**
     * Creates a new rate.
     *
     * @param CurrencyPairContract $currencyPair The currency pair
     * @param float                $value        The rate value
     * @param DateTimeInterface    $date         The date at which this rate was calculated
     * @param string               $providerName The class name of the provider that returned this rate
     */
    public function __construct(
        private CurrencyPairContract $currencyPair,
        private float                $value,
        private DateTimeInterface    $date,
        private string               $providerName
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @inheritDoc
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyPair(): CurrencyPairContract
    {
        return $this->currencyPair;
    }
}
