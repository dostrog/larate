<?php
declare(strict_types=1);

namespace Dostrog\Larate\Services;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair as CurrencyPairContract;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\ExchangeRate;
use Dostrog\Larate\StringHelper;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Carbon;
use NumberFormatter;
use RuntimeException;
use Throwable;

class RussianCentralBank extends HttpService
{
    public const string URL = 'https://www.cbr.ru/scripts/XML_daily.asp';
    public const string NAME = 'cbrf';

    public function __construct(?Factory $http = null)
    {
        parent::__construct($http);

        $this->url = self::URL;
        $this->serviceName = self::NAME;
    }

    public function parseRateData(string $content, string $quoteCurrency): array
    {
        $element = StringHelper::xmlToElement($content);

        if (empty($element['Date'])) {
            throw new RuntimeException(trans('larate::error.nodate'));
        }

        try {
            $date = Carbon::createFromFormat('!d.m.Y', (string) $element['Date']);
        } catch (Throwable $throwable) {
            throw new RuntimeException(trans('larate::error.badresponse', ['message' => $throwable->getMessage()]), $throwable->getCode(), $throwable);
        }

        $quoteCurrencyData = $element->xpath('./Valute[CharCode="' . $quoteCurrency . '"]');

        if (empty($quoteCurrencyData) || ! $date) {
            throw new RuntimeException(trans('larate::error.nocurrency', [
                'currency' => $quoteCurrency,
                'date' => $date->format('d/m/Y'),
            ]));
        }

        $valueStr = (string) $quoteCurrencyData['0']->Value;
        $fmt = NumberFormatter::create('ru_RU', NumberFormatter::PATTERN_DECIMAL);
        $value = $fmt->parse($valueStr);

        if ($value === false) {
            throw new RuntimeException(trans('larate::error.badfloat', ['value' => 'false']));
        }

        $nominalStr = (string) $quoteCurrencyData['0']->Nominal;
        $nominal = $fmt->parse($nominalStr);

        return [
            'value' => $value,
            'nominal' => $nominal,
            'date' => $date,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, ?DateTimeInterface $date = null): ExchangeRateContract
    {
        $quoteCurrency = $currencyPair->getQuoteCurrency();

        $content = ($date instanceof DateTimeInterface)
            ? $this->makeRequest(['date_req' => $date->format('d/m/Y')])
            : $this->makeRequest();

        ['value' => $value, 'nominal' => $nominal, 'date' => $responseDate] = $this->parseRateData($content, $quoteCurrency);

        return new ExchangeRate($currencyPair, $value / $nominal, $responseDate, $this->getName());
    }
}
