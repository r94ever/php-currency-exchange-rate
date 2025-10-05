<?php

namespace R94ever\CurrencyExchangeRate\Tests\Providers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\Enums\Currency;
use R94ever\CurrencyExchangeRate\ExchangeRateException;
use R94ever\CurrencyExchangeRate\HttpDrivers\HttpClientInterface;
use R94ever\CurrencyExchangeRate\HttpDrivers\HttpResponseInterface;
use R94ever\CurrencyExchangeRate\Providers\ExchangeRateHost;

class ExchangeRateHostTest extends TestCase
{
    private HttpClientInterface $httpClient;

    private ExchangeRateHost $provider;

    private HttpResponseInterface $response;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(HttpResponseInterface::class);
        $this->provider = new ExchangeRateHost('test_access_key', $this->httpClient);
    }

    #[Test]
    public function it_can_convert_currency_successfully()
    {
        $amount = 100.0;
        $from = Currency::USD;
        $to = Currency::EUR;
        $expectedResult = 85.5;

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'success' => true,
                'result' => $expectedResult,
            ]);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with(
                'https://api.exchangerate.host/convert',
                [
                    'access_key' => 'test_access_key',
                    'from' => 'USD',
                    'to' => 'EUR',
                    'amount' => $amount,
                ]
            )
            ->willReturn($this->response);

        $result = $this->provider->convert($amount, $from, $to);
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function it_throws_exception_on_api_error()
    {
        $amount = 100.0;
        $from = Currency::USD;
        $to = Currency::EUR;
        $errorMessage = 'Invalid API key';
        $errorCode = 101;

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'info' => $errorMessage,
                ],
            ]);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode($errorCode);

        $this->provider->convert($amount, $from, $to);
    }

    #[Test]
    public function it_handles_missing_result_in_response()
    {
        $amount = 100.0;
        $from = Currency::USD;
        $to = Currency::EUR;

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'success' => true,
                // Missing 'result' key
            ]);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $result = $this->provider->convert($amount, $from, $to);
        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_exception_with_unknown_error_when_error_info_missing()
    {
        $amount = 100.0;
        $from = Currency::USD;
        $to = Currency::EUR;

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'success' => false,
                // Missing error info
            ]);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->expectException(ExchangeRateException::class);
        $this->expectExceptionMessage('Unknown error');

        $this->provider->convert($amount, $from, $to);
    }

    public static function currencyPairProvider(): array
    {
        return [
            'GBP to JPY conversion' => [Currency::GBP, Currency::JPY, 100.0, 18250.0],
            'EUR to USD conversion' => [Currency::EUR, Currency::USD, 50.0, 53.5],
            'AUD to NZD conversion' => [Currency::AUD, Currency::NZD, 75.0, 82.5],
        ];
    }

    #[Test]
    #[DataProvider('currencyPairProvider')]
    public function it_handles_different_currency_pairs(
        Currency $from,
        Currency $to,
        float $amount,
        float $expectedResult
    ) {
        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'success' => true,
                'result' => $expectedResult,
            ]);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with(
                'https://api.exchangerate.host/convert',
                [
                    'access_key' => 'test_access_key',
                    'from' => $from->value,
                    'to' => $to->value,
                    'amount' => $amount,
                ]
            )
            ->willReturn($this->response);

        $result = $this->provider->convert($amount, $from, $to);
        $this->assertEquals($expectedResult, $result);
    }
}
