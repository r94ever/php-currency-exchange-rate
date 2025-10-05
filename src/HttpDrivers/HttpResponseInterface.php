<?php

namespace R94ever\CurrencyExchangeRate\HttpDrivers;

interface HttpResponseInterface
{
    public function getBody(): mixed;

    public function getStatusCode(): int;
}
