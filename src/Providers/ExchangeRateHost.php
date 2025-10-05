<?php

namespace R94ever\CurrencyExchangeRate\Providers;

use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface;

/**
 * @link https://www.exchangerate-api.com/
 */
class ExchangeRateHost extends BaseProvider
{
    protected const BASE_URL = 'https://api.exchangerate.host';

    public function __construct(private readonly string $accessKey, protected HttpClientInterface $httpClient)
    {
        parent::__construct();
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
        $response = $this->httpClient->get(self::BASE_URL.'/convert', [
            'access_key' => $this->accessKey,
            'from' => $from->value,
            'to' => $to->value,
            'amount' => $amount,
        ]);

        $responseBody = $response->getBody();
        $isSuccess = $responseBody['success'] ?? false;

        if (! $isSuccess) {
            $errorMsg = $responseBody['error']['info'] ?? 'Unknown error';
            $errorCode = $responseBody['error']['code'] ?? null;
            throw new ExchangeRateException($errorMsg, $errorCode);
        }

        return $responseBody['result'] ?? null;
    }
}
