<?php

namespace R94ever\CurrencyExchangeRate\Tests\HttpDrivers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\HttpDrivers\CurlHttpResponse;

class CurlHttpResponseTest extends TestCase
{
    #[Test]
    public function http_response_with_empty_body(): void
    {
        $response = new CurlHttpResponse('', 200);

        $this->assertEquals([], $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_null_body(): void
    {
        $response = new CurlHttpResponse(null, 204);

        $this->assertEquals([], $response->getBody());
        $this->assertEquals(204, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_json_body(): void
    {
        $data = ['message' => 'success', 'data' => ['key' => 'value']];
        $response = new CurlHttpResponse(json_encode($data), 200);

        $this->assertEquals($data, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_invalid_json_body(): void
    {
        $invalidJson = '{"key": "value",}'; // Invalid JSON with trailing comma
        $response = new CurlHttpResponse($invalidJson, 200);

        $this->assertEquals($invalidJson, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_plain_text_body(): void
    {
        $plainText = 'Hello, World!';
        $response = new CurlHttpResponse($plainText, 200);

        $this->assertEquals($plainText, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_error_status_code(): void
    {
        $errorMessage = 'Not Found';
        $response = new CurlHttpResponse($errorMessage, 404);

        $this->assertEquals($errorMessage, $response->getBody());
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function http_response_with_nested_json_body(): void
    {
        $data = [
            'data' => [
                'items' => [
                    ['id' => 1, 'name' => 'Item 1'],
                    ['id' => 2, 'name' => 'Item 2']
                ],
                'meta' => [
                    'total' => 2,
                    'page' => 1
                ]
            ]
        ];
        $response = new CurlHttpResponse(json_encode($data), 200);

        $this->assertEquals($data, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->getBody()['data']['items']);
        $this->assertCount(2, $response->getBody()['data']['items']);
    }

    #[Test]
    public function http_response_with_unicode_json_body(): void
    {
        $data = ['message' => 'Привет, мир! 你好，世界！'];
        $response = new CurlHttpResponse(json_encode($data), 200);

        $this->assertEquals($data, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Привет, мир! 你好，世界！', $response->getBody()['message']);
    }
}
