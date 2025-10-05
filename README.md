# Currency exchange rate package for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/r94ever/php-currency-exchange-rate.svg?style=flat-square)](https://packagist.org/packages/r94ever/php-currency-exchange-rate)
[![Tests](https://img.shields.io/github/actions/workflow/status/r94ever/php-currency-exchange-rate/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/r94ever/php-currency-exchange-rate/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/r94ever/php-currency-exchange-rate.svg?style=flat-square)](https://packagist.org/packages/r94ever/php-currency-exchange-rate)

This package provides a simple and easy way to get currency exchange rates from various providers.

## Installation

You can install the package via composer:

```bash
composer require r94ever/php-currency-exchange-rate
```

## Usage

```php
use R94ever\CurrencyExchangeRate\ExchangeRate;
use R94ever\CurrencyExchangeRate\Providers\ExchangeRateHost;
use R94ever\CurrencyExchangeRate\HttpDrivers\CurlHttpClient;
use R94ever\CurrencyExchangeRate\Enums\Currency;

// Create a new ExchangeRateHost provider with your access key.
$provider = new ExchangeRateHost('YOUR_ACCESS_KEY', new CurlHttpClient());

// Create a new ExchangeRate instance.
$exchangeRate = new ExchangeRate($provider);

// Convert 100 USD to EUR.
$result = $exchangeRate->convert(100, Currency::USD, Currency::EUR);

echo $result;
```

You can get your access key from [exchangerate.host](https://exchangerate.host/).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](./CONTRIBUTING.md) for details.

## Credits

- [Van Duong Thanh](https://github.com/r94ever)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
