<?php

namespace R94ever\CurrencyExchangeRate\Tests\HttpDrivers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use R94ever\CurrencyExchangeRate\HttpDrivers\CurlHttpClient;

class CurlHttpClientTest extends TestCase
{
    private CurlHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new CurlHttpClient();
    }

    #[Test]
    public function it_can_set_headers_for_request(): void
    {
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        $client = $this->client->withHeaders($headers);

        $response = $client->get('https://httpbin.org/headers');
        $body = $response->getBody();

        $this->assertIsArray($body);
        $this->assertArrayHasKey('headers', $body);
        $this->assertEquals('application/json', $body['headers']['Accept']);
    }

    #[Test]
    public function send_get_request_with_params(): void
    {
        $params = ['foo' => 'bar', 'test' => 'value'];
        $response = $this->client->get('https://httpbin.org/get', $params);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsArray($body);
        $this->assertArrayHasKey('args', $body);
        $this->assertEquals($params, $body['args']);
    }

    #[Test]
    public function send_get_request_without_params(): void
    {
        $response = $this->client->get('https://httpbin.org/get');

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsArray($body);
        $this->assertArrayHasKey('args', $body);
        $this->assertEmpty($body['args']);
    }

    #[Test]
    public function error_after_send_get_request(): void
    {
        $response = $this->client->get('https://invalid-domain-that-does-not-exist.test');

        $this->assertEquals(0, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());
    }

    #[Test]
    public function send_post_request_with_json_data(): void
    {
        $data = ['foo' => 'bar', 'test' => 'value'];
        $headers = ['Content-Type: application/json'];

        $response = $this->client
            ->withHeaders($headers)
            ->post('https://httpbin.org/post', json_encode($data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsArray($body);
        $this->assertArrayHasKey('json', $body);
        $this->assertEquals($data, $body['json']);
    }

    #[Test]
    public function send_post_request_with_form_data(): void
    {
        $data = ['foo' => 'bar', 'test' => 'value'];
        $response = $this->client->post('https://httpbin.org/post', $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsArray($body);
        $this->assertArrayHasKey('form', $body);
        $this->assertEquals($data, $body['form']);
    }

    #[Test]
    public function get_error_after_send_post_request(): void
    {
        $response = $this->client->post('https://invalid-domain-that-does-not-exist.test', ['test' => 'data']);

        $this->assertEquals(0, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());
    }
}
