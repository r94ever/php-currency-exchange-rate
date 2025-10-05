<?php

namespace R94ever\CurrencyExchangeRate\Tests\Stubs;

use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\Providers\BaseProvider;

class MockProvider extends BaseProvider
{
    protected float $rate = 1.5;

    public function convert(float $amount, Currency $from, Currency $to): ?float
    {
        return $amount * $this->rate;
    }
}
