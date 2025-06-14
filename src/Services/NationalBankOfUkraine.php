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

class NationalBankOfUkraine extends HttpService
{
    public const URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';
    public const NAME = 'nbu';

    public function __construct(?Factory $http = null)
    {
        parent::__construct($http);

        $this->url = self::URL;
        $this->serviceName = self::NAME;
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
        } catch (Throwable $throwable) {
            throw new RuntimeException(trans('larate::error.badresponse', ['message' => $throwable->getMessage()]), $throwable->getCode(), $throwable);
        }

        $valueStr = (string) $item->rate;

        if ($valueStr === '' || $valueStr === '0') {
            throw new RuntimeException(trans('larate::error.badfloat', ['value' => $valueStr]));
        }

        $value = $this->parseLocaleFloat($valueStr);

        return [
            'value' => $value,
            'date' => $date,
        ];
    }

    private function parseLocaleFloat($string, $locale = 'uk_UA')
    {
        // Remove any thousands separators first
        $string = \Safe\preg_replace('/[\s\xa0]/', '', $string); // Remove spaces and non-breaking spaces

        // Try NumberFormatter first
        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $result = $formatter->parse($string);
            if ($result !== false) {
                return $result;
            }
        }

        // Fallback: assume comma is decimal separator for Ukrainian
        return (float)str_replace(',', '.', $string);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyPairContract $currencyPair, ?DateTimeInterface $date = null): ExchangeRateContract
    {
        $quoteCurrency = $currencyPair->getQuoteCurrency();

        $content = ($date instanceof DateTimeInterface)
            ? $this->makeRequest([
                'date' => $date->format('Ymd'),
                'valcode' => $quoteCurrency,
            ])
            : $this->makeRequest(['valcode' => $quoteCurrency]);

        ['value' => $value, 'date' => $responseDate] = $this->parseRateData($content, $quoteCurrency);

        return new ExchangeRate($currencyPair, $value, $responseDate, $this->getName());
    }
}
