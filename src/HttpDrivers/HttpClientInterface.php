<?php

namespace R94ever\CurrencyExchangeRate\HttpDrivers;

interface HttpClientInterface
{
    public function withHeaders(array $headers): self;

    public function get(string $url): HttpResponseInterface;

    public function post(string $url, array $data): HttpResponseInterface;
}
