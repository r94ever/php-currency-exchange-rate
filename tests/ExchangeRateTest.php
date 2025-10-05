<?php

namespace R94ever\CurrencyExchangeRate\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\ExchangeRate;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\Providers\BaseProvider;

class ExchangeRateTest extends TestCase
{
    #[Test]
    public function it_throws_exception_when_source_currency_is_invalid()
    {
        $provider = $this->createMock(BaseProvider::class);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage('Invalid source currency code');

        (new ExchangeRate($provider))->convert(10, 'abc', 'VND');
    }

    #[Test]
    public function it_throws_exception_when_target_currency_is_invalid()
    {
        $provider = $this->createMock(BaseProvider::class);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage('Invalid target currency code');

        (new ExchangeRate($provider))->convert(10, 'EUR', 'EU1');
    }

    #[Test]
    public function it_can_change_the_provider()
    {
        $provider1 = $this->createMock(BaseProvider::class);
        $provider2 = $this->createMock(BaseProvider::class);

        $exchangeRate = new ExchangeRate($provider1);

        $this->assertSame($provider1, $exchangeRate->getProvider());

        $exchangeRate->useProvider($provider2);

        $this->assertSame($provider2, $exchangeRate->getProvider());
    }
}
