<?php

namespace R94ever\CurrencyExchangeRate\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\ProviderRegistry;
use R94ever\CurrencyExchangeRate\Tests\Stubs\MockProvider;

class ProviderRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ProviderRegistry::clear();
    }

    #[Test]
    public function it_test_can_register_provider()
    {
        ProviderRegistry::register('mock', MockProvider::class);
        $this->assertTrue(ProviderRegistry::has('mock'));
    }

    #[Test]
    public function it_throws_exception_on_registering_invalid_provider()
    {
        $this->expectException(InvalidArgumentException::class);
        ProviderRegistry::register('invalid', \stdClass::class);
    }

    #[Test]
    public function it_can_make_registered_provider()
    {
        ProviderRegistry::register('mock', MockProvider::class);
        $provider = ProviderRegistry::make('mock');

        $this->assertInstanceOf(MockProvider::class, $provider);
    }

    #[Test]
    public function it_throws_exception_on_making_unregistered_provider()
    {
        $this->expectException(ExchangeRateException::class);
        ProviderRegistry::make('unregistered');
    }

    #[Test]
    public function it_can_get_all_providers()
    {
        ProviderRegistry::register('mock1', MockProvider::class);
        ProviderRegistry::register('mock2', MockProvider::class);

        $providers = ProviderRegistry::getProviders();
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('mock1', $providers);
        $this->assertArrayHasKey('mock2', $providers);
    }

    #[Test]
    public function it_can_clear_providers()
    {
        ProviderRegistry::register('mock', MockProvider::class);
        ProviderRegistry::clear();

        $this->assertEmpty(ProviderRegistry::getProviders());
    }

    #[Test]
    public function provider_names_are_case_insensitive()
    {
        ProviderRegistry::register('MOCK', MockProvider::class);

        $this->assertTrue(ProviderRegistry::has('mock'));
        $this->assertTrue(ProviderRegistry::has('MOCK'));
        $this->assertTrue(ProviderRegistry::has('Mock'));

        $provider = ProviderRegistry::make('mock');
        $this->assertInstanceOf(MockProvider::class, $provider);
    }
}
