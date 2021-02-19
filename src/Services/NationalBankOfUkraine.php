<?php
declare(strict_types=1);

namespace Dostrog\Larate\Services;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateService;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\StringHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
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
            throw new RuntimeException(trans('larate::error.request', ['provider' => self::NAME]));
        }

        if ($response->failed()) {
            throw new RuntimeException(trans('larate::error.request', ['provider' => self::NAME]));
        }

        return $response->body();
    }

    public function parseRateData(string $content, string $quoteCurrency): array
    {
        $element = StringHelper::xmlToElement($content);

        $elements = $element->xpath('./currency[cc="' . $quoteCurrency . '"]');

        if (empty($elements)) {
            throw new RuntimeException(trans('larate::error.nocurrency', ['currency' => $quoteCurrency]));
        }

        $item = $elements[0];

        try {
            $date = Carbon::createFromFormat('!d.m.Y', (string) $item->exchangedate);
        } catch (Throwable $th) {
            // todo: log error
            throw new RuntimeException(trans('larate::error.badresponse', ['message' => $th->getMessage()]));
        }

        $valueStr = (string) $item->rate;
        $fmt = NumberFormatter::create('ua_UA', NumberFormatter::DECIMAL);
        $value = $fmt->parse($valueStr);

        if ($value === false) {
            throw new RuntimeException(trans('larate::error.badfloat', ['value' => $value]));
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
