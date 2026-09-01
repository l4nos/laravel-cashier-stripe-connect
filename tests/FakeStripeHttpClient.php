<?php

namespace Lanos\CashierConnect\Tests;

use Stripe\HttpClient\ClientInterface;

// stripe-php renamed HttpClientInterface to ClientInterface in v15.
// Alias whichever exists so the suite runs against Cashier 14-16.
if (interface_exists(ClientInterface::class)) {
    interface StripeHttpClientContract extends ClientInterface
    {
    }
} else {
    interface StripeHttpClientContract extends \Stripe\HttpClient\HttpClientInterface
    {
    }
}

/**
 * Fake Stripe HTTP client. Records every request and returns queued JSON
 * responses, allowing tests to assert on payload composition without
 * any network access.
 */
class FakeStripeHttpClient implements StripeHttpClientContract
{
    /** @var array<int, array{method: string, url: string, headers: array, params: array}> */
    public array $requests = [];

    /** @var array<int, array{0: array, 1: int}> */
    protected array $responses = [];

    /**
     * Queue a response body (as an array) to be returned for the next request.
     */
    public function queueResponse(array $body, int $status = 200): self
    {
        $this->responses[] = [$body, $status];

        return $this;
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        $this->requests[] = [
            'method' => $method,
            'url' => $absUrl,
            'headers' => $headers,
            'params' => $params,
        ];

        [$body, $status] = array_shift($this->responses) ?: [['id' => 'fake_object'], 200];

        return [json_encode($body), $status, []];
    }

    public function lastRequest(): ?array
    {
        return end($this->requests) ?: null;
    }

    public function lastParams(): array
    {
        return $this->lastRequest()['params'] ?? [];
    }

    /**
     * Assert-friendly header lookup. Headers are full strings e.g. "Stripe-Account: acct_123".
     */
    public function lastHeader(string $name): ?string
    {
        foreach ($this->lastRequest()['headers'] ?? [] as $header) {
            if (stripos($header, $name.':') === 0) {
                return trim(substr($header, strlen($name) + 1));
            }
        }

        return null;
    }
}
