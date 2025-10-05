<?php

namespace R94ever\CurrencyExchangeRate\Providers;

use R94ever\CurrencyExchangeRate\CurrencyRate;
use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\HttpDrivers\CurlHttpClient;
use R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface;

/**
 * @link https://www.exchangerate-api.com/
 */
class ExchangeRateHost extends BaseProvider implements UseHttpClientInterface
{
    protected const BASE_URL = 'https://api.exchangerate.host';

    private ?HttpClientInterface $httpClient = null;

    public function __construct(private readonly string $accessKey)
    {
        parent::__construct();

        $this->useHttpClient(new CurlHttpClient());
    }

    public function useHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param  float  $amount  The amount to convert.
     * @param  Currency|string  $from  The source currency.
     * @param  Currency|string  $to  The target currency.
     * @return float|null The converted amount, or null if the conversion failed.
     *
     * @throws ExchangeRateException If the conversion failed.
     */
    public function convert(float $amount, Currency|string $from, Currency|string $to): ?float
    {
        $response = $this->getHttpClient()->get(self::BASE_URL.'/convert', [
            'access_key' => $this->accessKey,
            'from' => $from->value,
            'to' => $to->value,
            'amount' => $amount,
        ]);

        $responseBody = $response->getBody();
        $isSuccess = $responseBody['success'] ?? false;

        if (!$isSuccess) {
            $errorMsg = $responseBody['error']['info'] ?? 'Unknown error';
            $errorCode = $responseBody['error']['code'] ?? 0;
            throw new ExchangeRateException($errorMsg, $errorCode);
        }

        return $responseBody['result'] ?? null;
    }

    /**
     * Get the exchange rates for a source currency and multiple target currencies.
     *
     * @param  Currency  $source  The source currency.
     * @param  array<Currency>  $targets  The target currencies.
     * @return array<CurrencyRate>  The exchange rates, or null if the request failed.
     *
     * @throws ExchangeRateException If the request failed.
     */
    public function getRates(Currency $source, array $targets): array
    {
        $currencies = array_map(
            fn (Currency $currency) => $currency->value,
            $targets
        );
        $currencies = join(',', $currencies);

        $response = $this->getHttpClient()->get(self::BASE_URL.'/live', [
            'access_key' => $this->accessKey,
            'source' => $source->value,
            'currencies' => $currencies,
        ]);

        $responseBody = $response->getBody();
        $isSuccess = $responseBody['success'] ?? false;

        if (!$isSuccess) {
            $errorMsg = $responseBody['error']['info'] ?? 'Unknown error';
            $errorCode = $responseBody['error']['code'] ?? 0;
            throw new ExchangeRateException($errorMsg, $errorCode);
        }

        return $this->normalizeRates($source, $responseBody['quotes']);
    }

    /**
     * Normalize the exchange rates from the API response.
     *
     * The API response rates are in the format of 'XXXYYY', where
     * - 'XXX' is the source currency, and
     * - 'YYY' is the exchange rate.
     *
     * This function normalizes the exchange rates by creating a new array of
     * `CurrencyRate` objects, where each object has the source currency, target
     * currency, and the exchange rate.
     *
     * @param Currency $source The source currency.
     * @param array<string, float> $ratesFromResponse The exchange rates from the API response.
     * @return array<CurrencyRate> The normalized exchange rates.
     */
    public function normalizeRates(Currency $source, array $ratesFromResponse): array
    {
        $normalizedRates = [];

        foreach ($ratesFromResponse as $symbol => $rate) {
            $parsed = str_split($symbol, 3);

            $normalizedRates[] = new CurrencyRate(
                source: $source,
                target: Currency::from(strtoupper($parsed[1])),
                rate: $rate,
            );
        }

        return $normalizedRates;
    }
}
