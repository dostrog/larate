# Access to CBRF currencies rates from Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dostrog/larate.svg?style=flat-square)](https://packagist.org/packages/dostrog/larate)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/dostrog/larate/run-tests?label=tests/master)](https://github.com/dostrog/larate/workflows/Tests/badge.svg?branch=master)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/dostrog/larate/run-tests?label=tests/develop)](https://github.com/dostrog/larate/workflows/Tests/badge.svg?branch=develop)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/dostrog/larate/Check%20&%20fix%20styling?label=code%20style)](https://github.com/dostrog/larate/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/dostrog/larate.svg?style=flat-square)](https://packagist.org/packages/dostrog/larate)


## Overview

A simple Laravel package used for interacting with the [Bank of Russia](https://www.cbr.ru/development/SXML/) and [National Bank of Ukraine](https://bank.gov.ua/ua/open-data/api-dev) API. 'Larate' allow you to get the latest or historical converted monetary values between RUB (UAH) and other currencies.

## Installation

You can install the package via composer:

```bash
composer require dostrog/larate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Dostrog\Larate\Providers\LarateServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'default_base_currency' => 'RUB',
    'service' => [
        'RUB' => Dostrog\Larate\Services\RussianCentralBank::class,
        'UAH' => Dostrog\Larate\Services\NationalBankOfUkraine::class,
    ],
];
```

## Usage

```php
// instantiate from Laravel IoC for inject provider
// according to config it maybe RUB (converted) rates from Central Bank OF Russia
$provider = app()->make(Larate::class);
$pair = new CurrencyPair('RUB', 'USD');
$date = Carbon::parse('2020-01-16');
$rate = $provider->getExchangeRate($pair, $date);
$value = $rate->getValue(); // 61.4328

// ...or using factory method, i.e. for getting UAH (converted) rates from National Bank of Ukraine
$provider = Larate::createForBaseCurrency('UAH');
$pair = new CurrencyPair('UAH', 'USD');
$date = Carbon::parse('2020-01-16');
$rate = $provider->getExchangeRate($pair, $date);
$value = $rate->getValue(); // 23.9821

// ...or using Laravel's Facades
$rate = LarateFacade::getExchangeRate($pair, $date);
$value = $rate->getValue();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sergey Donin](https://github.com/dostrog)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
