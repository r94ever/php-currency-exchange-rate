<?php

namespace R94ever\CurrencyExchangeRate;

use R94ever\CurrencyExchangeRate\Enums\Currency;

class CurrencyRate
{
    public function __construct(public Currency $source, public Currency $target, public float $rate)
    {
        //
    }
}
