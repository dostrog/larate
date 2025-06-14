<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Contracts\ExchangeRateService;
use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Services\NationalBankOfUkraine;
use Dostrog\Larate\Tests\TestCase;
use Exception;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class NationalBankOfUkraineTest extends TestCase
{
    /**
     * @var string
     */
    public const BASE_CURRENCY = 'UAH';
    /**
     * @var string
     */
    public const QUOTE_CURRENCY = 'USD';
    /**
     * @var string
     */
    public const PROVIDER_NAME = 'nbu';
    /**
     * @var string
     */
    public const DATE = '2020-01-16';
    public ExchangeRateService $service;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->service = new NationalBankOfUkraine();
    }

    #[Test]
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

        self::assertEquals([
            'value' => 23.9821,
            'date' => Carbon::parse('16.01.2020'),
        ], $this->service->parseRateData($content, 'USD'));
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $a = $this->service->parseRateData($content, 'USD');
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $this->service->parseRateData($content, 'USD');
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $this->service->parseRateData($content, 'USD');
    }

    #[Test]
    public function nbu_get_exchange_rate_for_non_holiday(): void
    {
        $date = '2020-01-16';
        $expected = 23.9821;

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        self::assertEquals($expected, $this->service->getExchangeRate($pair, Carbon::parse($date))->getValue());
    }

    //    #[Test]
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

    #[Test]
    public function nbu_get_exchange_rate_for_no_currency_on_period(): void
    {
        $date = '1969-01-16';
        $quoteCurrency = 'USD';

        $pair = new CurrencyPair(self::BASE_CURRENCY, $quoteCurrency);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::parse($date));
    }

    #[Test]
    public function nbu_get_exchange_rate_for_holiday(): void
    {
        $date0 = '2020-01-01';
        $date1 = '2020-01-02';
        $expected = 23.6862;

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $rate0 = $this->service->getExchangeRate($pair, Carbon::parse($date0))->getValue();
        $rate1 = $this->service->getExchangeRate($pair, Carbon::parse($date1))->getValue();

        self::assertEquals($expected, $rate0);
        self::assertEquals($rate0, $rate1);
    }

    #[Test]
    public function nbu_get_exchange_rate_for_future(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::now()->addYear());
    }

    #[Test]
    public function nbu_get_exchange_rate_for_past(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::now()->subYears(50));
    }

    #[Test]
    public function rcb_get_latest_exchange_rate(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);
        $rate = $this->service->getExchangeRate($pair);

        self::assertIsFloat($rate->getValue());
    }

    #[Test]
    public function rcb_get_exchange_rate_response_throw_exception(): void
    {
        $httpClient = $this->mock(Factory::class);
        $httpClient->shouldReceive('get')
            ->withSomeOfArgs(['url' => NationalBankOfUkraine::URL])
            ->andThrow(Exception::class);

        $rcb = new NationalBankOfUkraine($httpClient);
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair);
    }

    #[Test]
    public function nbu_get_exchange_rate_response_failed(): void
    {
        $httpClient = Http::fake(fn ($request) => Http::response([], 500));

        $rcb = new NationalBankOfUkraine($httpClient);
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $rcb->getExchangeRate($pair);
    }

    #[Test]
    public function nbu_get_name(): void
    {
        self::assertEquals(self::PROVIDER_NAME, $this->service->getName());
    }
}
