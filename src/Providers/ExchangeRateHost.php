<?php

namespace R94ever\CurrencyExchangeRate\Providers;

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

        if (! $isSuccess) {
            $errorMsg = $responseBody['error']['info'] ?? 'Unknown error';
            $errorCode = $responseBody['error']['code'] ?? 0;
            throw new ExchangeRateException($errorMsg, $errorCode);
        }

        return $responseBody['result'] ?? null;
    }
}
