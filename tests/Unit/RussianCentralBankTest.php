<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Services\RussianCentralBank;
use Dostrog\Larate\Tests\TestCase;
use Illuminate\Support\Carbon;
use RuntimeException;

class RussianCentralBankTest extends TestCase
{
    public const BASE_CURRENCY = 'RUB';
    public const QUOTE_CURRENCY = 'USD';
    public const PROVIDER_NAME = 'cbrf';
    public const DATE = '2020-01-16';

    /** @test */
    public function rcb_parse_rate_data(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs Date="16.01.1996" name="Foreign Currency Market">
    <Valute ID="R01795">
    <NumCode>233</NumCode>
    <CharCode>EEK</CharCode>
    <Nominal>1</Nominal>
    <Name>Эстонская крона</Name>
    <Value>418,1800</Value>
    </Valute>
</ValCurs>
CONTENT;

        $rcb = new RussianCentralBank();

        self::assertEquals([418.1800, 1.0, Carbon::parse('16.01.1996')], $rcb->parseRateData($content, 'EEK'));
    }

    /** @test */
    public function rcb_parse_rate_data_no_value(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs Date="16.01.1996" name="Foreign Currency Market">
    <Valute ID="R01795">
        <NumCode>233</NumCode>
        <CharCode>EEK</CharCode>
        <Nominal>1</Nominal>
        <Name>Эстонская крона</Name>
        <Value>foo418,1800</Value>
    </Valute>
</ValCurs>
CONTENT;

        $rcb = new RussianCentralBank();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'EEK');
    }

    /** @test */
    public function rcb_parse_rate_data_invalid_date(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs Date="16.01.1996foo" name="Foreign Currency Market">
    <Valute ID="R01795">
        <NumCode>233</NumCode>
        <CharCode>EEK</CharCode>
        <Nominal>1</Nominal>
        <Name>Эстонская крона</Name>
        <Value>foo418,1800</Value>
    </Valute>
</ValCurs>
CONTENT;

        $rcb = new RussianCentralBank();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'EEK');
    }

    /** @test */
    public function rcb_parse_rate_data_wo_date(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs foo="16.01.1996" name="Foreign Currency Market">
    <Valute ID="R01795">
    <NumCode>233</NumCode>
    <CharCode>EEK</CharCode>
    <Nominal>1</Nominal>
    <Name>Эстонская крона</Name>
    <Value>418,1800</Value>
    </Valute>
</ValCurs>
CONTENT;

        $rcb = new RussianCentralBank();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'EEK');
    }

    /** @test */
    public function rcb_get_exchange_rate_for_non_holiday(): void
    {
        $date = '2020-01-16';
        $expected = 61.4328;

        $rcb = new RussianCentralBank();

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        self::assertEquals($expected, $rcb->getExchangeRate($pair, Carbon::parse($date))->getValue());
    }

    /**
     * @test
     *
     */
//    public function rcb_get_latest_exchange_rate(): void
//    {
//        // CBRF set rates at 12
//        if (Carbon::now()->hour <= 12) {
//            return;
//        }
//
//        $rcb = new RussianCentralBank();
//        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
//
//        $v1 = $rcb->getExchangeRate($pair, Carbon::now()->addDay())->getValue();
//        $v2 = $rcb->getExchangeRate($pair)->getValue();
//
//        self::assertEquals($v1, $v2);
//    }

    /** @test */
    public function rcb_get_exchange_rate_for_no_currency_on_period(): void
    {
        $date = '1996-01-16';
        $quoteCurrency = 'USD';

        $rcb = new RussianCentralBank();
        $pair = new CurrencyPair(self::BASE_CURRENCY, $quoteCurrency);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::parse($date));
    }

    /** @test */
    public function rcb_get_exchange_rate_for_holiday(): void
    {
        $date0 = '2020-01-01';
        $date1 = '2020-01-02';
        $expected = 61.9057;

        $rcb = new RussianCentralBank();

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $rate0 = $rcb->getExchangeRate($pair, Carbon::parse($date0))->getValue();
        $rate1 = $rcb->getExchangeRate($pair, Carbon::parse($date1))->getValue();

        self::assertEquals($expected, $rate0);
        self::assertEquals($rate0, $rate1);
    }

    /** @test */
    public function rcb_get_exchange_rate_for_future(): void
    {
        $rcb = new RussianCentralBank();
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::now()->addYear());
    }

    /** @test */
    public function rcb_get_exchange_rate_for_past(): void
    {
        $rcb = new RussianCentralBank();
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::now()->subYears(50));
    }

    /** @test */
    public function rcb_get_name(): void
    {
        $rcb = new RussianCentralBank();

        self::assertEquals(self::PROVIDER_NAME, $rcb->getName());
    }
}
