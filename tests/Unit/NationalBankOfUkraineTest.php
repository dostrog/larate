<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Services\NationalBankOfUkraine;
use Dostrog\Larate\Tests\TestCase;
use Dostrog\Larate\CurrencyPair;
use Illuminate\Support\Carbon;
use RuntimeException;

class NationalBankOfUkraineTest extends TestCase
{
    public const BASE_CURRENCY = 'UAH';
    public const QUOTE_CURRENCY = 'USD';
    public const PROVIDER_NAME = 'nbu';
    public const DATE = '2020-01-16';

    /** @test */
    public function nbu_parse_rate_data(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<exchange>
  <currency>
    <r030>840</r030>
    <txt>Долар США</txt>
    <rate>23.9821</rate>
    <cc>USD</cc>
    <exchangedate>16.01.2020</exchangedate>
</currency>
</exchange>
CONTENT;

        $rcb = new NationalBankOfUkraine();

        self::assertEquals([23.9821, Carbon::parse('16.01.2020')], $rcb->parseRateData($content, 'USD'));
    }

    /** @test */
    public function rcb_parse_rate_data_no_value(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<exchange>
  <currency>
    <r030>840</r030>
    <txt>Долар США</txt>
    <ratefoo>23.9821</ratefoo>
    <cc>USD</cc>
    <exchangedate>16.01.2020</exchangedate>
</currency>
</exchange>
CONTENT;

        $rcb = new NationalBankOfUkraine();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'USD');
    }

    /** @test */
    public function rcb_parse_rate_data_invalid_date(): void
    {
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<exchange>
  <currency>
    <r030>840</r030>
    <txt>Долар США</txt>
    <rate>23.9821</rate>
    <cc>USD</cc>
    <exchangedate>foo16.01.2020</exchangedate>
</currency>
</exchange>
CONTENT;

        $rcb = new NationalBankOfUkraine();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'USD');
    }

    /** @test */
    public function nbu_parse_rate_data_wo_date(): void
    {

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<exchange>
  <currency>
    <r030>840</r030>
    <txt>Долар США</txt>
    <rate>23.9821</rate>
    <cc>USD</cc>
    <exchangedate1>16.01.2020</exchangedate1>
</currency>
</exchange>
CONTENT;

        $rcb = new NationalBankOfUkraine();

        $this->expectException(RuntimeException::class);
        $rcb->parseRateData($content, 'USD');
    }


    /** @test */
    public function nbu_get_exchange_rate_for_non_holiday(): void
    {
        $date = '2020-01-16';
        $expected = 23.9821;

        $rcb = new NationalBankOfUkraine();

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        self::assertEquals($expected, $rcb->getExchangeRate($pair, Carbon::parse($date))->getValue());
    }

    /**
     * @test
     *
     */
//    public function nbu_get_latest_exchange_rate(): void
//    {
//        if (Carbon::now()->hour <= 12) {
//            return;
//        }
//
//        $rcb = new NationalBankOfUkraine();
//        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
//
//        $v1 = $rcb->getExchangeRate($pair, Carbon::now()->addDay())->getValue();
//        $v2 = $rcb->getExchangeRate($pair)->getValue();
//
//        self::assertEquals($v1, $v2);
//    }

    /** @test */
    public function nbu_get_exchange_rate_for_no_currency_on_period(): void
    {
        $date = '1969-01-16';
        $quoteCurrency = 'USD';

        $rcb = new NationalBankOfUkraine();
        $pair = new CurrencyPair(self::BASE_CURRENCY, $quoteCurrency);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::parse($date));
    }

    /** @test */
    public function nbu_get_exchange_rate_for_holiday(): void
    {
        $date0 = '2020-01-01';
        $date1 = '2020-01-02';
        $expected = 23.6862;

        $rcb = new NationalBankOfUkraine();

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $rate0 = $rcb->getExchangeRate($pair, Carbon::parse($date0))->getValue();
        $rate1 = $rcb->getExchangeRate($pair, Carbon::parse($date1))->getValue();

        self::assertEquals($expected, $rate0);
        self::assertEquals($rate0, $rate1);
    }

    /** @test */
    public function nbu_get_exchange_rate_for_future(): void
    {
        $rcb = new NationalBankOfUkraine();
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::now()->addYear());
    }

    /** @test */
    public function nbu_get_exchange_rate_for_past(): void
    {
        $rcb = new NationalBankOfUkraine();
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair, Carbon::now()->subYears(50));
    }

    /** @test */
    public function nbu_get_name(): void
    {
        $rcb = new NationalBankOfUkraine();

        self::assertEquals(self::PROVIDER_NAME, $rcb->getName());
    }
}
