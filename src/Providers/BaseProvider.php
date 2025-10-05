<?php

namespace R94ever\CurrencyExchangeRate\Providers;

use R94ever\CurrencyExchangeRate\Enums\Currency;

abstract class BaseProvider implements ExchangeRateProviderInterface
{
    public function __construct()
    {
        //
    }

    abstract public function convert(float $amount, Currency $from, Currency $to): ?float;

    abstract public function getRates(Currency $source, array $targets): array;
}
