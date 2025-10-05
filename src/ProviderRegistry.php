<?php

namespace R94ever\CurrencyExchangeRate;

use InvalidArgumentException;
use R94ever\CurrencyExchangeRate\Providers\ExchangeRateProviderInterface;

class ProviderRegistry
{
    /**
     * @var array<string, class-string<ExchangeRateProviderInterface>>
     */
    protected static array $providers = [];

    /**
     * Register a new exchange rate provider
     *
     * @param  class-string<ExchangeRateProviderInterface>  $providerClass
     */
    public static function register(string $name, string $providerClass): void
    {
        if (!is_subclass_of($providerClass, ExchangeRateProviderInterface::class)) {
            throw new InvalidArgumentException(
                'Provider class must implement '.ExchangeRateProviderInterface::class
            );
        }

        static::$providers[strtolower($name)] = $providerClass;
    }

    /**
     * Create a provider instance by name
     *
     * @throws ExchangeRateException
     */
    public static function make(string $name): ExchangeRateProviderInterface
    {
        $name = strtolower($name);

        if (! self::has($name)) {
            throw ExchangeRateException::providerNotFound($name);
        }

        $providerClass = static::$providers[$name];

        return new $providerClass();
    }

    /**
     * Check if a provider is registered
     */
    public static function has(string $name): bool
    {
        return isset(static::$providers[strtolower($name)]);
    }

    /**
     * Get all registered providers
     *
     * @return array<string, class-string<ExchangeRateProviderInterface>>
     */
    public static function getProviders(): array
    {
        return static::$providers;
    }

    /**
     * Clear all registered providers
     */
    public static function clear(): void
    {
        static::$providers = [];
    }
}
