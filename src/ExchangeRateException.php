<?php

namespace R94ever\CurrencyExchangeRate;

use Exception;

class ExchangeRateException extends Exception
{
    public static function invalidSourceCurrency(): self
    {
        return new self('Invalid source currency code');
    }

    public static function invalidTargetCurrency(): self
    {
        return new self('Invalid target currency code');
    }
}
