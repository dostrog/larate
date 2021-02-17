<?php
declare(strict_types=1);

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\CurrencyPair;
use RuntimeException;
use Illuminate\Support\Carbon;
use Dostrog\Larate\CacheHelper;
use Dostrog\Larate\StringHelper;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;

class StringHelperTest extends TestCase
{

    public function test_convert_empty_string_to_xml(): void
    {
        self::assertInstanceOf(SimpleXMLElement::class, StringHelper::xmlToElement(''));
    }

    public function test_convert_bad_string_to_xml(): void
    {
        $this->expectException(RuntimeException::class);
        StringHelper::xmlToElement('foo');
    }
}
