# Currency exchange rate package for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/r94ever/php-currency-exchange-rate.svg?style=flat-square)](https://packagist.org/packages/r94ever/php-currency-exchange-rate)
[![Tests](https://github.com/r94ever/php-currency-exchange-rate/actions/workflows/run-tests.yml/badge.svg)](https://github.com/r94ever/php-currency-exchange-rate/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/github/license/r94ever/laravel-media.svg)](https://github.com/r94ever/laravel-media/blob/master/LICENSE.md)

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
use R94ever\CurrencyExchangeRate\Enums\Currency;

// Create a new ExchangeRateHost provider with your access key.
$provider = new ExchangeRateHost('YOUR_ACCESS_KEY');

// Create a new ExchangeRate instance.
$exchangeRate = new ExchangeRate($provider);

// Convert 100 USD to EUR.
$result = $exchangeRate->convert(100, Currency::USD, Currency::EUR);

echo $result;
```

## Current Supported Providers

-   [ExchangeRateHost](https://exchangerate.host)

## Extending

This package is designed to be extensible. You can easily create a new provider to fetch rates from another source or
use your own HTTP Client.

### Creating a Custom Provider

To fetch exchange rate data from another API, you just need to create a new class that extends `BaseProvider` and
implement the `convert` method.

1.  **Create your Provider class:**

    ```php
    <?php

    namespace App\CurrencyProviders;

    use R94ever\CurrencyExchangeRate\Providers\BaseProvider;
    use R94ever\CurrencyExchangeRate\Enums\Currency;

    class MyCustomProvider extends BaseProvider
    {
        public function convert(float $amount, Currency $from, Currency $to): ?float
        {
            // Write your logic here to call the API and get the rate
            // Example:
            if ($from === Currency::USD && $to === Currency::VND) {
                $rate = 25000; // Assumed rate
                return $amount * $rate;
            }

            return null;
        }
    }
    ```

2.  **Use the new Provider:**

    Instantiate `ExchangeRate` with your custom provider.

    ```php
    use R94ever\CurrencyExchangeRate\ExchangeRate;
    use App\CurrencyProviders\MyCustomProvider;

    $provider = new MyCustomProvider();
    $exchangeRate = new ExchangeRate($provider);

    $result = $exchangeRate->convert(10, Currency::USD, Currency::VND); // 250000
    ```

### Using a Custom HTTP Client

If you want to use a different HTTP Client (e.g., Guzzle, which is already in your project) instead of the default cURL
wrapper, you can create an adapter class that implements the `HttpClientInterface`.

1.  **Create your HTTP Client class:**

    This class must implement `R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface`.

    ```php
    <?php

    namespace App\HttpClients;

    use R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface;
    use R94ever\CurrencyExchangeRate\HttpDrivers\HttpResponseInterface;
    use R94ever\CurrencyExchangeRate\HttpDrivers\CurlHttpResponse; // Or create your own HttpResponse

    class MyCustomHttpClient implements HttpClientInterface
    {
        // Assuming you inject another client, e.g., Guzzle
        private $guzzleClient;

        public function __construct()
        {
            // $this->guzzleClient = new GuzzleHttp\Client();
        }

        public function withHeaders(array $headers): self
        {
            // Logic to add headers to the next request
            // $this->guzzleClient->setDefaultOption('headers', $headers);
            return $this;
        }

        public function get(string $url): HttpResponseInterface
        {
            // Logic to perform a GET request with your client
            // $response = $this->guzzleClient->get($url);
            // $body = $response->getBody()->getContents();
            // $statusCode = $response->getStatusCode();

            // Temporarily return a mock response
            $body = '{"success": true, "rates": {"USD": 1.08}}';
            $statusCode = 200;

            return new CurlHttpResponse($body, $statusCode);
        }

        public function post(string $url, array $data): HttpResponseInterface
        {
            // Logic to perform a POST request
            // ...
            $body = '{"success": true}';
            $statusCode = 200;
            return new CurlHttpResponse($body, $statusCode);
        }
    }
    ```
    
2.  **Create your HttpResponse class:**

    ```php
    <?php

    namespace App\HttpClients;

    use R94ever\CurrencyExchangeRate\HttpDrivers\HttpResponseInterface;

    class MyCustomHttpResponse implements HttpResponseInterface
    {
        private $body;
        private $statusCode;

        public function __construct($body, $statusCode)
        {
            $this->body = $body;
            $this->statusCode = $statusCode;
        }

        public function getBody(): string
        {
            return $this->body;
        }

        public function getStatusCode(): int
        {
            return $this->statusCode;
        }
    }
    ```

3.  **Inject the HTTP Client into the Provider:**

    When instantiating a provider that requires HTTP requests (like `ExchangeRateHost`), pass the instance of your custom client into it.

    ```php
    use R94ever\CurrencyExchangeRate\Providers\ExchangeRateHost;
    use App\HttpClients\MyCustomHttpClient;

    $customHttpClient = new MyCustomHttpClient();
    $provider = new ExchangeRateHost('YOUR_ACCESS_KEY');
    $provider->useHttpClient($customHttpClient);

    // Then, use the provider as usual
    $exchangeRate = new ExchangeRate($provider);
    $result = $exchangeRate->convert(100, Currency::USD, Currency::EUR);
    ```


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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
