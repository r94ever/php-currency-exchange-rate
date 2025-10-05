<?php

namespace R94ever\CurrencyExchangeRate\Providers;

use R94ever\CurrencyExchangeRate\CurrencyRate;
use R94ever\CurrencyExchangeRate\Enums\Currency;

interface ExchangeRateProviderInterface
{
    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount The amount to convert.
     * @param Currency $from The source currency.
     * @param Currency $to The target currency.
     * @return float|null The converted amount, or null if the conversion failed.
     */
    public function convert(float $amount, Currency $from, Currency $to): ?float;

    /**
     * Get the exchange rates for a source currency and multiple target currencies.
     *
     * @param Currency $source The source currency.
     * @param array<Currency> $targets The target currencies.
     * @return array<CurrencyRate> The exchange rates, or null if the request failed.
     */
    public function getRates(Currency $source, array $targets): array;
}
