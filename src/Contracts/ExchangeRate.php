<?php
declare(strict_types=1);

namespace Dostrog\Larate\Contracts;

use DateTimeInterface;

interface ExchangeRate
{
    /**
     * Gets the rate value.
     */
    public function getValue(): float;

    /**
     * Gets the date at which this rate was set by provider
     */
    public function getDate(): DateTimeInterface;

    /**
     * Gets the name of the provider that returned this rate.
     */
    public function getProviderName(): string;

    /**
     * Gets the currency pair.
     */
    public function getCurrencyPair(): CurrencyPair;
}
