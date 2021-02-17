<?php
declare(strict_types=1);

namespace Dostrog\Larate\Services;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Dostrog\Larate\StringHelper;
use NumberFormatter;
use RuntimeException;
use Throwable;


class NationalBankOfUkraine implements ExchangeRateService
{
    public const NAME = 'nbu';
    public const URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    protected function makeRequest(array $params = null): string
    {
        try {
            $response = (isset($params))
                ? Http::get(self::URL, $params)
                : Http::get(self::URL);
        } catch (Throwable $th) {
            // todo: log error
            throw new RuntimeException("Error requesting provider: " . self::NAME);
        }

        if ($response->failed()) {
            throw new RuntimeException("Error requesting provider " . self::NAME);
        }

        return $response->body();
    }

    public function parseRateData(string $content, string $quoteCurrency): array
    {
        $element = StringHelper::xmlToElement($content);

        $elements = $element->xpath('./currency[cc="' . $quoteCurrency . '"]');

        if (empty($elements)) {
            throw new RuntimeException("Unexpected response: no requested currency '{$quoteCurrency}' in server response.");
        }

        $item = $elements[0];

        try {
            $date = Carbon::createFromFormat('!d.m.Y', (string) $item->exchangedate);
        } catch (Throwable $th) {
            // todo: log error
            throw new RuntimeException("Unexpected response: " . $th->getMessage());
        }

        $valueStr = (string) $item->rate;
        $fmt = NumberFormatter::create( 'ua_UA', NumberFormatter::DECIMAL );
        $value = $fmt->parse($valueStr);

        if ($value === false) {
            throw new RuntimeException("Ошибка преобразования строки '{$value}' в тип float. Невозможно импортировать.");
        }

        return [$value, $date];
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, DateTimeInterface $date = null): ExchangeRateContract
    {
        $quoteCurrency = $currencyPair->getQuoteCurrency();

        $content = isset($date)
            ? $this->makeRequest([
                'date' => $date->format('Ymd'),
                'valcode' => $quoteCurrency,
            ])
            : $this->makeRequest(['valcode' => $quoteCurrency]);

        [$value, $responseDate] = $this->parseRateData($content, $quoteCurrency);

        return new ExchangeRate($currencyPair, $value, $responseDate, $this->getName());
    }
}
