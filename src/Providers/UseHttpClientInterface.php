<?php

namespace R94ever\CurrencyExchangeRate\Providers;

use R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface;

interface UseHttpClientInterface
{
    public function useHttpClient(HttpClientInterface $httpClient): self;

    public function getHttpClient(): HttpClientInterface;
}
