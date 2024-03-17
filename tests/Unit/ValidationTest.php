<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\Validation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

final class ValidationTest extends TestCase
{
    /**
     * @var string
    */
    public const string BASE_CURRENCY = 'EUR';
    /**
     * @var string
     */
    public const string QUOTE_CURRENCY = 'USD';

    #[Test]
    public function currency_code_validation_not_iso4217(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('foo');
    }

    #[Test]
    public function currency_code_validation_not_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('');
    }

    #[Test]
    public function currency_code_validation_is_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::validateCurrencyCode('!@#');
    }
}
