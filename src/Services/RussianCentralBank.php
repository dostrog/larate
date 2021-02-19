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

class RussianCentralBank implements ExchangeRateService
{
    public const NAME = 'cbrf';
    public const URL = 'https://www.cbr.ru/scripts/XML_daily.asp';

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

        if (empty($element['Date'])) {
            throw new RuntimeException(trans('larate::error.nodate'));
        }

        try {
            $date = Carbon::createFromFormat('!d.m.Y', (string) $element['Date']);
        } catch (Throwable $th) {
            // todo: log error
            throw new RuntimeException(trans('larate::error.badresponse', ['message' => $th->getMessage()]));
        }

        $quoteCurrencyData = $element->xpath('./Valute[CharCode="' . $quoteCurrency . '"]');

        if (empty($quoteCurrencyData) || ! $date) {
            throw new RuntimeException(trans('larate::error.nocurrency', [
                'currency' => $quoteCurrency,
                'date' => $date->format('d/m/Y'),
            ]));
        }

        $valueStr = (string) $quoteCurrencyData['0']->Value;
        $fmt = NumberFormatter::create('ru_RU', NumberFormatter::DECIMAL);
        $value = $fmt->parse($valueStr);

        if ($value === false) {
            throw new RuntimeException(trans('larate::error.badfloat', ['value' => $value]));
        }

        $nominalStr = (string) $quoteCurrencyData['0']->Nominal;
        $nominal = $fmt->parse($nominalStr);

        return [$value, $nominal, $date];
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, DateTimeInterface $date = null): ExchangeRateContract
    {
        $quoteCurrency = $currencyPair->getQuoteCurrency();

        $content = isset($date)
            ? $this->makeRequest(['date_req' => $date->format('d/m/Y')])
            : $this->makeRequest();

        [$value, $nominal, $responseDate] = $this->parseRateData($content, $quoteCurrency);

        return new ExchangeRate($currencyPair, $value / $nominal, $responseDate, $this->getName());
    }
}
