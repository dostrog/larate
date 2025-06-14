<?php
declare(strict_types=1);

namespace Dostrog\Larate\Services;

use DateTimeInterface;
use Dostrog\Larate\Contracts\CurrencyPair;
use Dostrog\Larate\Contracts\ExchangeRate as ExchangeRateContract;
use Dostrog\Larate\Contracts\ExchangeRateService;
use Dostrog\Larate\Exceptions\HttpServiceException;
use Illuminate\Http\Client\Factory;
use Throwable;

abstract class HttpService implements ExchangeRateService
{
    public string $serviceName = '';
    public string $url = '';
    public Factory $http;

    public function __construct(?Factory $http = null)
    {
        $this->http = $http ?? new Factory();
    }

    /**
     * @inheritDoc
     */
    abstract public function getExchangeRate(CurrencyPair $currencyPair, ?DateTimeInterface $date = null): ExchangeRateContract;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->serviceName;
    }

    protected function makeRequest(?array $params = null): string
    {
        try {
            $response = (isset($params))
                ? $this->http->get($this->url, $params)
                : $this->http->get($this->url);
        } catch (Throwable $throwable) {
            throw new HttpServiceException(trans('larate::error.request', ['provider' => $this->serviceName]), $throwable->getCode(), $throwable);
        }

        // Laravel HTTP client does not throw exception, check for error with this
        if ($response->failed()) {
            throw new HttpServiceException(trans('larate::error.request', ['provider' => $this->serviceName]));
        }

        return $response->body();
    }
}
