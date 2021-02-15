<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Tests\TestCase;
use InvalidArgumentException;

class CurrencyPairTest extends TestCase
{
    public const BASE_CURRENCY = 'EUR';
    public const QUOTE_CURRENCY = 'USD';

    /** @test */
    public function factory_method_is_correct(): void
    {
        $pair = CurrencyPair::createFromString(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY);
        self::assertInstanceOf(CurrencyPair::class, $pair);
    }

    /** @test */
    public function constructor_is_correct(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::BASE_CURRENCY);
        self::assertInstanceOf(CurrencyPair::class, $pair);
    }

    /** @test */
    public function getters_is_correct(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::BASE_CURRENCY);
        self::assertTrue($pair->isIdentical());

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
        self::assertNotTrue($pair->isIdentical());

        self::assertSame(self::BASE_CURRENCY, $pair->getBaseCurrency());
        self::assertSame(self::QUOTE_CURRENCY, $pair->getQuoteCurrency());
    }

    /** @test */
    public function invalid_argument_exception_is_trow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $pair = CurrencyPair::createFromString(self::BASE_CURRENCY . '+' . self::BASE_CURRENCY);
    }

    /** @test */
    public function toString_method_is_correct_implemented(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::BASE_CURRENCY);
        self::assertSame(self::BASE_CURRENCY . '/' . self::QUOTE_CURRENCY, $pair->__toString());
    }
}
