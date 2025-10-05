<?php

namespace R94ever\CurrencyExchangeRate\HttpDrivers;

class CurlHttpClient implements HttpClientInterface
{
    private array $headers = [];

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function get(string $url, ?array $params = []): HttpResponseInterface
    {
        $ch = curl_init();

        if (! empty($params)) {
            $queryString = http_build_query($params);
            $url .= $queryString ? '?'.$queryString : '';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $output = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return new CurlHttpResponse($error, 0);
        }

        curl_close($ch);

        return new CurlHttpResponse($output, $statusCode);
    }

    public function post(string $url, array|string|null $data): HttpResponseInterface
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $output = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return new CurlHttpResponse($error, 0);
        }

        curl_close($ch);

        return new CurlHttpResponse($output, $statusCode);
    }
}
