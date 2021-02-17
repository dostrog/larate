<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;

final class ExchangeRate implements ExchangeRateContract
{
    /**
     * The currency pair.
     */
    private CurrencyPairContract $currencyPair;

    /**
     * The value.
     */
    private float $value;

    /**
     * The date.
     */
    private DateTimeInterface $date;

    /**
     * The provider.
     */
    private string $providerName;

    /**
     * Creates a new rate.
     *
     * @param CurrencyPairContract $currencyPair The currency pair
     * @param float                $value        The rate value
     * @param DateTimeInterface    $date         The date at which this rate was calculated
     * @param string               $providerName The class name of the provider that returned this rate
     */
    public function __construct(CurrencyPairContract $currencyPair, float $value, DateTimeInterface $date, string $providerName)
    {
        $this->currencyPair = $currencyPair;
        $this->value = $value;
        $this->date = $date;
        $this->providerName = $providerName;
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
