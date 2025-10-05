<?php

namespace R94ever\CurrencyExchangeRate\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\CurrencyRate;
use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\ExchangeRate;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\ProviderRegistry;
use R94ever\CurrencyExchangeRate\Providers\ExchangeRateProviderInterface;
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
        $result = (new ExchangeRate())->resolveCurrencyParam('USD');
        $this->assertSame(Currency::USD, $result);
    }

    #[Test]
    public function it_can_convert_using_currency_enums()
    {
        $result = (new ExchangeRate())->resolveCurrencyParam(Currency::GBP);
        $this->assertSame(Currency::GBP, $result);
    }

    #[Test]
    public function it_handles_case_insensitive_currency_codes()
    {
        $result = (new ExchangeRate())->resolveCurrencyParam('cad');
        $this->assertEquals(Currency::CAD, $result);
    }

    #[Test]
    public function it_throws_exception_when_source_currency_is_invalid()
    {
        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage('Invalid currency code');

        (new ExchangeRate())->resolveCurrencyParam('abc');
    }

    #[Test]
    public function it_can_change_the_provider()
    {
        $provider1 = $this->createMock(ExchangeRateProviderInterface::class);
        $provider2 = $this->createMock(ExchangeRateProviderInterface::class);

        $exchangeRate = new ExchangeRate($provider1);

        $this->assertSame($provider1, $exchangeRate->getProvider());

        $exchangeRate->useProvider($provider2);

        $this->assertSame($provider2, $exchangeRate->getProvider());
    }

    #[Test]
    public function it_can_use_registered_provider_by_name()
    {
        ProviderRegistry::register('mock', MockProvider::class);

        $exchangeRate = new ExchangeRate();
        $exchangeRate->useProvider('mock');

        $this->assertInstanceOf(MockProvider::class, $exchangeRate->getProvider());
    }

    #[Test]
    public function it_throws_exception_when_using_unregistered_provider()
    {
        $exchangeRate = new ExchangeRate();

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage("Exchange rate provider 'unregistered' not found");
        $exchangeRate->useProvider('unregistered');
    }

    #[Test]
    public function it_works_with_custom_provider()
    {
        ProviderRegistry::register('mock', MockProvider::class);

        $exchangeRate = new ExchangeRate();
        $exchangeRate->useProvider('mock');

        $result = $exchangeRate->convert(100.0, Currency::USD, Currency::EUR);
        $this->assertEquals(150.0, $result);
    }

    #[Test]
    public function it_can_get_rates_with_enum_currencies()
    {
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $source = Currency::USD;
        $targets = [Currency::EUR, Currency::GBP, Currency::JPY];

        $expectedRates = [
            new CurrencyRate(Currency::USD, Currency::EUR, 0.85),
            new CurrencyRate(Currency::USD, Currency::GBP, 0.73),
            new CurrencyRate(Currency::USD, Currency::JPY, 110.25),
        ];

        $provider->expects($this->once())
            ->method('getRates')
            ->with($source, $targets)
            ->willReturn($expectedRates);

        $exchangeRate = new ExchangeRate($provider);
        $result = $exchangeRate->getRates($source, $targets);

        $this->assertEquals($expectedRates, $result);
    }

    #[Test]
    public function it_can_get_rates_with_string_currencies()
    {
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $expectedRates = [
            new CurrencyRate(Currency::USD, Currency::EUR, 0.85),
            new CurrencyRate(Currency::USD, Currency::GBP, 0.73),
        ];

        $provider->expects($this->once())
            ->method('getRates')
            ->with(
                Currency::USD,
                [Currency::EUR, Currency::GBP]
            )
            ->willReturn($expectedRates);

        $exchangeRate = new ExchangeRate($provider);
        $result = $exchangeRate->getRates('USD', [Currency::EUR, Currency::GBP]);

        $this->assertEquals($expectedRates, $result);
    }

    #[Test]
    public function it_throws_exception_when_source_currency_is_invalid_in_get_rates()
    {
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $exchangeRate = new ExchangeRate($provider);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage('Invalid currency code');

        $exchangeRate->getRates('INVALID', [Currency::EUR, Currency::GBP]);
    }

    #[Test]
    public function it_passes_provider_exceptions_through_in_get_rates()
    {
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $errorMessage = 'API Error';

        $provider->expects($this->once())
            ->method('getRates')
            ->willThrowException(new ExchangeRateException($errorMessage));

        $exchangeRate = new ExchangeRate($provider);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage($errorMessage);

        $exchangeRate->getRates(Currency::USD, [Currency::EUR, Currency::GBP]);
    }

    #[Test]
    public function it_works_with_custom_provider_for_get_rates()
    {
        ProviderRegistry::register('mock', MockProvider::class);

        $exchangeRate = new ExchangeRate();
        $exchangeRate->useProvider('mock');

        $result = $exchangeRate->getRates(Currency::USD, [Currency::EUR, Currency::GBP]);

        $this->assertIsArray($result);
        foreach ($result as $rate) {
            $this->assertInstanceOf(CurrencyRate::class, $rate);
            $this->assertEquals(Currency::USD, $rate->source);
            $this->assertContains($rate->target, [Currency::EUR, Currency::GBP]);
        }
    }
}
