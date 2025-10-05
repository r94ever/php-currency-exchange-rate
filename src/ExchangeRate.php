<?php

namespace R94ever\CurrencyExchangeRate;

use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\Providers\ExchangeRateProviderInterface;

class ExchangeRate
{
    public function __construct(protected ?ExchangeRateProviderInterface $provider = null)
    {
        //
    }

    /**
     * Set the exchange rate provider to use.
     *
     * You can pass either an instance of `ExchangeRateProviderInterface`
     * or a string representing the name of a registered provider.
     *
     * @param ExchangeRateProviderInterface|string $provider The provider to use.
     * @return self
     * @throws ExchangeRateException
     */
    public function useProvider(ExchangeRateProviderInterface|string $provider): self
    {
        if (is_string($provider)) {
            $this->provider = ProviderRegistry::make($provider);
        } else {
            $this->provider = $provider;
        }

        return $this;
    }

    public function getProvider(): ExchangeRateProviderInterface
    {
        return $this->provider;
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param  float  $amount  The amount to convert.
     * @param  string|Currency  $from  The source currency.
     * @param  string|Currency  $to  The target currency.
     * @return float The converted amount.
     *
     * @throws ExchangeRateException If the conversion failed.
     */
    public function convert(float $amount, string|Currency $from, string|Currency $to): float
    {
        $from = $this->resolveCurrencyParam($from);
        $to = $this->resolveCurrencyParam($to);

        return $this->provider->convert($amount, $from, $to);
    }

    /**
     * Get the exchange rates for a source currency and multiple target currencies.
     *
     * @param string|Currency $source The source currency.
     * @param array<Currency> $targets The target currencies.
     * @return array<CurrencyRate> The exchange rates
     *
     * @throws ExchangeRateException If the request failed.
     */
    public function getRates(string|Currency $source, array $targets): array
    {
        $source = $this->resolveCurrencyParam($source);

        return $this->provider->getRates($source, $targets);
    }

    /**
     * Resolve a currency parameter into a Currency object.
     *
     * @param string|Currency $currency The currency parameter to resolve.
     * @return Currency The resolved currency object.
     *
     * @throws ExchangeRateException If the currency parameter is invalid.
     */
    public function resolveCurrencyParam(string|Currency $currency): Currency
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        if ($currency = Currency::tryFrom(strtoupper($currency))) {
            return $currency;
        }

        throw ExchangeRateException::invalidCurrency();
    }
}
