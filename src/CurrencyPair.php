<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use InvalidArgumentException;

final class CurrencyPair implements CurrencyPairContract
{
    /**
     * The base currency.
     */
    private string $baseCurrency;

    /**
     * The quote currency.
     */
    private string $quoteCurrency;

    /**
     * Creates a new currency pair.
     *
     * @param string $baseCurrency  The base currency ISO 4217 code
     * @param string $quoteCurrency The quote currency ISO 4217 code
     */
    public function __construct(string $baseCurrency, string $quoteCurrency)
    {
        Validation::validateCurrencyCode($baseCurrency);
        Validation::validateCurrencyCode($quoteCurrency);

        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;
    }

    /**
     * Creates a currency pair from a string.
     *
     * @param string $string A string in the form EUR/USD
     *
     * @throws InvalidArgumentException
     *
     * @return CurrencyPairContract
     */
    public static function createFromString(string $string): CurrencyPairContract
    {
        $matches = [];
        if (!preg_match('#^([A-Z0-9]{3,})/([A-Z0-9]{3,})$#', $string, $matches)) {
            throw new InvalidArgumentException('The currency pair must be in the form "EUR/USD".');
        }

        $parts = explode('/', $string);

        return new self($parts[0], $parts[1]);
    }

    /**
     * @inheritDoc
     */
    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    /**
     * @inheritDoc
     */
    public function getQuoteCurrency(): string
    {
        return $this->quoteCurrency;
    }

    /**
     * @inheritDoc
     */
    public function isIdentical(): bool
    {
        return $this->baseCurrency === $this->quoteCurrency;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return sprintf('%s/%s', $this->baseCurrency, $this->quoteCurrency);
    }
}
