<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Contracts\ExchangeRateService;
use Dostrog\Larate\CurrencyPair;
use Dostrog\Larate\Exceptions\HttpServiceException;
use Dostrog\Larate\Services\RussianCentralBank;
use Dostrog\Larate\Tests\TestCase;
use Exception;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class RussianCentralBankTest extends TestCase
{
    /**
     * @var string
     */
    public const BASE_CURRENCY = 'RUB';
    /**
     * @var string
     */
    public const QUOTE_CURRENCY = 'USD';
    /**
     * @var string
     */
    public const PROVIDER_NAME = 'cbrf';
    /**
     * @var string
     */
    public const DATE = '2020-01-16';
    public ExchangeRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RussianCentralBank();
    }

    #[Test]
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

        self::assertEquals([
            'value' => 418.1800, 'nominal' => 1.0, 'date' => Carbon::parse('16.01.1996'),
        ], $this->service->parseRateData($content, 'EEK'));
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $this->service->parseRateData($content, 'EEK');
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $this->service->parseRateData($content, 'EEK');
    }

    #[Test]
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

        $this->expectException(RuntimeException::class);
        $this->service->parseRateData($content, 'EEK');
    }

    #[Test]
    public function rcb_get_exchange_rate_for_non_holiday(): void
    {
        $date = '2020-01-16';
        $expected = 61.4328;

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        try {
            self::assertEquals($expected, $this->service->getExchangeRate($pair, Carbon::parse($date))->getValue());
        } catch (HttpServiceException $e) {
            $this->markTestIncomplete("External API error: " . $e->getMessage());
        }
    }

    #[Test]
    public function rcb_get_exchange_rate_for_no_currency_on_period(): void
    {
        $date = '1996-01-16';
        $quoteCurrency = 'USD';

        $pair = new CurrencyPair(self::BASE_CURRENCY, $quoteCurrency);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::parse($date));
    }

    #[Test]
    public function rcb_get_exchange_rate_for_holiday(): void
    {
        $date0 = '2020-01-01';
        $date1 = '2020-01-02';
        $expected = 61.9057;

        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        try {
            $rate0 = $this->service->getExchangeRate($pair, Carbon::parse($date0))->getValue();
            $rate1 = $this->service->getExchangeRate($pair, Carbon::parse($date1))->getValue();
        } catch (HttpServiceException $e) {
            $this->markTestIncomplete("External API error: " . $e->getMessage());
        }

        self::assertEquals($expected, $rate0);
        self::assertSame($rate0, $rate1);
    }

    #[Test]
    public function rcb_get_exchange_rate_for_future(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::now()->addYear());
    }

    #[Test]
    public function rcb_get_latest_exchange_rate(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        try {
            $rate = $this->service->getExchangeRate($pair);
        } catch (HttpServiceException $e) {
            $this->markTestIncomplete("External API error: " . $e->getMessage());
        }

        self::assertIsFloat($rate->getValue());
    }

    #[Test]
    public function rcb_get_exchange_rate_response_throw_exception(): void
    {
        $httpClient = $this->mock(Factory::class);
        $httpClient->shouldReceive('get')
            ->withSomeOfArgs(['url' => RussianCentralBank::URL])
            ->andThrow(Exception::class);


        $rcb = new RussianCentralBank($httpClient);
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(HttpServiceException::class);
        $rcb->getExchangeRate($pair);
    }

    #[Test]
    public function rcb_get_exchange_rate_response_failed(): void
    {
        $httpClient = Http::fake(fn ($request) => Http::response([], 500));

        $rcb = new RussianCentralBank($httpClient);
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(HttpServiceException::class);
        $rcb->getExchangeRate($pair);
    }

    #[Test]
    public function rcb_get_exchange_rate_for_past(): void
    {
        $pair = new CurrencyPair(self::BASE_CURRENCY, self::QUOTE_CURRENCY);

        $this->expectException(RuntimeException::class);
        $this->service->getExchangeRate($pair, Carbon::now()->subYears(50));
    }

    #[Test]
    public function rcb_get_name(): void
    {
        self::assertSame(self::PROVIDER_NAME, $this->service->getName());
    }
}
