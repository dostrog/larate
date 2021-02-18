<?php
declare(strict_types=1);

namespace Dostrog\Larate\Contracts;

interface CurrencyPair
{
    /**
     * Get the base currency.
     *
     * @return string
     */
    public function getBaseCurrency(): string;

    /**
     * Gets the quote currency.
     *
     * @return string
     */
    public function getQuoteCurrency(): string;

    /**
     * Checks if the pair is identical.
     *
     * @return bool
     */
    public function isIdentical(): bool;
}
