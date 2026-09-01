<?php

namespace Lanos\CashierConnect\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lanos\CashierConnect\Http\Middleware\VerifyConnectWebhook;
use Lanos\CashierConnect\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyConnectWebhookTest extends TestCase
{
    protected function makeRequest(string $payload, ?string $signatureHeader): Request
    {
        $request = Request::create('/connectWebhook', 'POST', [], [], [], [], $payload);

        if ($signatureHeader !== null) {
            $request->headers->set('Stripe-Signature', $signatureHeader);
        }

        return $request;
    }

    public function test_valid_signature_passes(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded', 'data' => []]);

        $request = $this->makeRequest($payload, $this->signPayload($payload));

        $response = (new VerifyConnectWebhook)->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertSame('OK', $response->getContent());
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded']);

        $request = $this->makeRequest($payload, 't='.time().',v1=invalidsignature');

        $this->expectException(AccessDeniedHttpException::class);

        (new VerifyConnectWebhook)->handle($request, fn ($req) => new Response('OK'));
    }

    public function test_missing_signature_header_is_rejected(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded']);

        $request = $this->makeRequest($payload, null);

        $this->expectException(AccessDeniedHttpException::class);

        (new VerifyConnectWebhook)->handle($request, fn ($req) => new Response('OK'));
    }

    public function test_signature_with_wrong_secret_is_rejected(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded']);

        $request = $this->makeRequest($payload, $this->signPayload($payload, null, 'whsec_wrong_secret'));

        $this->expectException(AccessDeniedHttpException::class);

        (new VerifyConnectWebhook)->handle($request, fn ($req) => new Response('OK'));
    }

    public function test_expired_timestamp_is_rejected(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded']);
        $oldTimestamp = time() - 600; // beyond default 300s tolerance

        $request = $this->makeRequest($payload, $this->signPayload($payload, $oldTimestamp));

        $this->expectException(AccessDeniedHttpException::class);

        (new VerifyConnectWebhook)->handle($request, fn ($req) => new Response('OK'));
    }

    public function test_custom_tolerance_is_respected(): void
    {
        config()->set('cashierconnect.webhook.tolerance', 900);

        $payload = json_encode(['type' => 'charge.succeeded']);
        $timestamp = time() - 600; // outside default 300s, inside custom 900s

        $request = $this->makeRequest($payload, $this->signPayload($payload, $timestamp));

        $response = (new VerifyConnectWebhook)->handle($request, fn ($req) => new Response('OK'));

        $this->assertSame('OK', $response->getContent());
    }
}
