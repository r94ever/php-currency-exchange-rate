<?php

namespace R94ever\CurrencyExchangeRate\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\ExchangeRate;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\ProviderRegistry;
use R94ever\CurrencyExchangeRate\Providers\BaseProvider;
use R94ever\CurrencyExchangeRate\Tests\Stubs\MockProvider;

class ExchangeRateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ProviderRegistry::clear();
    }

    #[Test]
    public function it_can_convert_using_string_currency_codes()
    {
        $provider = $this->createMock(BaseProvider::class);
        $provider->expects($this->once())
            ->method('convert')
            ->with(100.0, Currency::USD, Currency::EUR)
            ->willReturn(85.0);

        $result = (new ExchangeRate($provider))->convert(100.0, 'USD', 'EUR');
        $this->assertEquals(85.0, $result);
    }

    #[Test]
    public function it_can_convert_using_currency_enums()
    {
        $provider = $this->createMock(BaseProvider::class);
        $provider->expects($this->once())
            ->method('convert')
            ->with(100.0, Currency::GBP, Currency::JPY)
            ->willReturn(18250.0);

        $result = (new ExchangeRate($provider))->convert(100.0, Currency::GBP, Currency::JPY);
        $this->assertEquals(18250.0, $result);
    }

    #[Test]
    public function it_can_convert_using_mixed_string_and_enum_currencies()
    {
        $provider = $this->createMock(BaseProvider::class);
        $provider->expects($this->once())
            ->method('convert')
            ->with(50.0, Currency::AUD, Currency::SGD)
            ->willReturn(45.0);

        $result = (new ExchangeRate($provider))->convert(50.0, Currency::AUD, 'SGD');
        $this->assertEquals(45.0, $result);
    }

    #[Test]
    public function it_handles_case_insensitive_currency_codes()
    {
        $provider = $this->createMock(BaseProvider::class);
        $provider->expects($this->once())
            ->method('convert')
            ->with(200.0, Currency::CAD, Currency::NZD)
            ->willReturn(220.0);

        $result = (new ExchangeRate($provider))->convert(200.0, 'cad', 'nzd');
        $this->assertEquals(220.0, $result);
    }

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

    #[Test]
    public function it_can_use_registered_provider_by_name()
    {
        ProviderRegistry::register('mock', MockProvider::class);

        $exchangeRate = new ExchangeRate;
        $exchangeRate->useProvider('mock');

        $this->assertInstanceOf(MockProvider::class, $exchangeRate->getProvider());
    }

    #[Test]
    public function it_throws_exception_when_using_unregistered_provider()
    {
        $exchangeRate = new ExchangeRate;

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage("Exchange rate provider 'unregistered' not found");
        $exchangeRate->useProvider('unregistered');
    }

    #[Test]
    public function it_works_with_custom_provider()
    {
        ProviderRegistry::register('mock', MockProvider::class);

        $exchangeRate = new ExchangeRate;
        $exchangeRate->useProvider('mock');

        $result = $exchangeRate->convert(100.0, Currency::USD, Currency::EUR);
        $this->assertEquals(150.0, $result);
    }
}
