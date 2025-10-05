<?php

namespace R94ever\CurrencyExchangeRate;

use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\Providers\BaseProvider;

class ExchangeRate
{
    public function __construct(protected BaseProvider $provider)
    {
        //
    }

    public function useProvider(BaseProvider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider(): BaseProvider
    {
        return $this->provider;
    }

    public function convert(float $amount, string|Currency $from, string|Currency $to): float
    {
        if (is_string($from)) {
            $from = Currency::tryFrom(strtoupper($from));

            if (! $from instanceof Currency) {
                throw ExchangeRateException::invalidSourceCurrency();
            }
        }

        if (is_string($to)) {
            $to = Currency::tryFrom(strtoupper($to));

            if (! $to instanceof Currency) {
                throw ExchangeRateException::invalidTargetCurrency();
            }
        }

        return $this->provider->convert($amount, $from, $to);
    }
}
