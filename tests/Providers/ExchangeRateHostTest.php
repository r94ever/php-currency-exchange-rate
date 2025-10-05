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

    private string $testAccessKey = 'test_access_key';

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(HttpResponseInterface::class);
        $this->provider = new ExchangeRateHost($this->testAccessKey);
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
                    'access_key' => $this->testAccessKey,
                    'from' => 'USD',
                    'to' => 'EUR',
                    'amount' => $amount,
                ]
            )
            ->willReturn($this->response);

        $result = $this->provider
            ->useHttpClient($this->httpClient)
            ->convert($amount, $from, $to);
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

        $this->provider
            ->useHttpClient($this->httpClient)
            ->convert($amount, $from, $to);
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

        $result = $this->provider
            ->useHttpClient($this->httpClient)
            ->convert($amount, $from, $to);

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

        $this->provider
            ->useHttpClient($this->httpClient)
            ->convert($amount, $from, $to);
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
                    'access_key' => $this->testAccessKey,
                    'from' => $from->value,
                    'to' => $to->value,
                    'amount' => $amount,
                ]
            )
            ->willReturn($this->response);

        $result = $this->provider
            ->useHttpClient($this->httpClient)
            ->convert($amount, $from, $to);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function it_can_get_multiple_rates_successfully()
    {
        $source = Currency::USD;
        $targets = [Currency::EUR, Currency::GBP, Currency::JPY];
        $responseData = [
            'success' => true,
            'quotes' => [
                'USDEUR' => 0.85,
                'USDGBP' => 0.73,
                'USDJPY' => 110.25,
            ],
        ];

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($responseData);

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with(
                'https://api.exchangerate.host/live',
                [
                    'access_key' => $this->testAccessKey,
                    'source' => $source->value,
                    'currencies' => 'EUR,GBP,JPY',
                ]
            )
            ->willReturn($this->response);

        $rates = $this->provider
            ->useHttpClient($this->httpClient)
            ->getRates($source, $targets);

        $this->assertCount(3, $rates);

        // Check each rate
        foreach ($rates as $rate) {
            $this->assertEquals($source, $rate->source);
            $this->assertContains($rate->target, $targets);
            $this->assertEquals(
                $responseData['quotes']['USD' . $rate->target->value],
                $rate->rate
            );
        }
    }

    #[Test]
    public function it_throws_exception_on_get_rates_api_error()
    {
        $source = Currency::USD;
        $targets = [Currency::EUR, Currency::GBP];
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

        $this->provider
            ->useHttpClient($this->httpClient)
            ->getRates($source, $targets);
    }

    #[Test]
    public function it_throws_exception_with_unknown_error_when_get_rates_error_info_missing()
    {
        $source = Currency::USD;
        $targets = [Currency::EUR, Currency::GBP];

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

        $this->provider
            ->useHttpClient($this->httpClient)
            ->getRates($source, $targets);
    }

    #[Test]
    public function it_normalizes_rates_correctly()
    {
        $source = Currency::USD;
        $ratesFromResponse = [
            'USDEUR' => 0.85,
            'USDGBP' => 0.73,
            'USDJPY' => 110.25,
        ];

        $normalizedRates = $this->provider->normalizeRates($source, $ratesFromResponse);

        $this->assertCount(3, $normalizedRates);

        $expectedRates = [
            ['target' => Currency::EUR, 'rate' => 0.85],
            ['target' => Currency::GBP, 'rate' => 0.73],
            ['target' => Currency::JPY, 'rate' => 110.25],
        ];

        foreach ($normalizedRates as $index => $rate) {
            $this->assertEquals($source, $rate->source);
            $this->assertEquals($expectedRates[$index]['target'], $rate->target);
            $this->assertEquals($expectedRates[$index]['rate'], $rate->rate);
        }
    }
}
