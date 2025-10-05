<?php

namespace R94ever\CurrencyExchangeRate\HttpDrivers;

class CurlHttpResponse implements HttpResponseInterface
{
    private mixed $body;

    public function __construct(mixed $body, protected int $statusCode)
    {
        $this->body = $this->parseBody($body);
    }

    protected function parseBody(mixed $body): array|string|null
    {
        if (!$body) {
            return [];
        }

        $parsedBody = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsedBody;
        }

        return $body;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
