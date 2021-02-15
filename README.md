# Access to CBRF currencies rates from Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dostrog/larate.svg?style=flat-square)](https://packagist.org/packages/dostrog/larate)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/dostrog/larate/run-tests?label=tests)](https://github.com/dostrog/larate/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/dostrog/larate/Check%20&%20fix%20styling?label=code%20style)](https://github.com/dostrog/larate/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/dostrog/larate.svg?style=flat-square)](https://packagist.org/packages/dostrog/larate)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

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
];
```

## Usage

```php
$larate = new Dostrog\Larate();
echo $larate->echoPhrase('Hello, Dostrog!');
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
