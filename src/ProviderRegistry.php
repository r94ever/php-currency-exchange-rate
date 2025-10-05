<?php

namespace R94ever\CurrencyExchangeRate;

use R94ever\CurrencyExchangeRate\Providers\BaseProvider;

class ProviderRegistry
{
    /**
     * @var array<string, class-string<BaseProvider>>
     */
    protected static array $providers = [];

    /**
     * Register a new exchange rate provider
     *
     * @param string $name
     * @param class-string<BaseProvider> $providerClass
     * @return void
     */
    public static function register(string $name, string $providerClass): void
    {
        if (!is_subclass_of($providerClass, BaseProvider::class)) {
            throw new \InvalidArgumentException(
                "Provider class must extend " . BaseProvider::class
            );
        }

        static::$providers[strtolower($name)] = $providerClass;
    }

    /**
     * Create a provider instance by name
     *
     * @param string $name
     * @return BaseProvider
     * @throws ExchangeRateException
     */
    public static function make(string $name): BaseProvider
    {
        $name = strtolower($name);

        if (!self::has($name)) {
            throw ExchangeRateException::providerNotFound($name);
        }

        $providerClass = static::$providers[$name];
        return new $providerClass();
    }

    /**
     * Check if a provider is registered
     *
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(static::$providers[strtolower($name)]);
    }

    /**
     * Get all registered providers
     *
     * @return array<string, class-string<BaseProvider>>
     */
    public static function getProviders(): array
    {
        return static::$providers;
    }

    /**
     * Clear all registered providers
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$providers = [];
    }
}
