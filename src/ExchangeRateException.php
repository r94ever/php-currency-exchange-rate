<?php

namespace R94ever\CurrencyExchangeRate;

use Exception;

class ExchangeRateException extends Exception
{
    public static function invalidCurrency(): self
    {
        return new self('Invalid currency code');
    }

    public static function providerNotFound(string $name): self
    {
        return new self("Exchange rate provider '$name' not found");
    }
}
