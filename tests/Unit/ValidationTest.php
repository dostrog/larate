<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\Validation;
use InvalidArgumentException;

final class ValidationTest extends TestCase
{
    /**
     * @var string
    */
    public const BASE_CURRENCY = 'EUR';
    /**
     * @var string
     */
    public const QUOTE_CURRENCY = 'USD';

    /** @test */
    public function currency_code_validation_not_iso4217(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('foo');
    }

    /** @test */
    public function currency_code_validation_not_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('');
    }

    /** @test */
    public function currency_code_validation_is_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('!@#');
    }
}
