<?php
declare(strict_types=1);

namespace Dostrog\Larate\Contracts;

interface CurrencyPair
{
    /**
     * Get the base currency.
     */
    public function getBaseCurrency(): string;

    /**
     * Gets the quote currency.
     */
    public function getQuoteCurrency(): string;

    /**
     * Checks if the pair is identical.
     */
    public function isIdentical(): bool;
}
