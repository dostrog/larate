<?php
declare(strict_types=1);

namespace Dostrog\Larate\Contracts;

use DateTimeInterface;

interface ExchangeRate
{
    /**
     * Gets the rate value.
     *
     * @return float
     */
    public function getValue(): float;

    /**
     * Gets the date at which this rate was set by provider
     *
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface;


    /**
     * Gets the name of the provider that returned this rate.
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Gets the currency pair.
     *
     * @return CurrencyPair
     */
    public function getCurrencyPair(): CurrencyPair;

//    /**
//     * Get all requested currency rates.
//     *
//     * @return array
//     */
//    public function getRates();
//
//    /**
//     * Get the rate for the given currency.
//     * Must return null if currency is not found in the result.
//     *
//     * @param string $currency
//     * @return float|null
//     */
//    public function getRate($currency);
//
//    /**
//     * Get all requested currency conversions.
//     *
//     * @return array
//     */
//    public function getConverted();
}
