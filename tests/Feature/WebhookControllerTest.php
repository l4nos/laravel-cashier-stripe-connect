<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Lanos\CashierConnect\Events\ConnectWebhookHandled;
use Lanos\CashierConnect\Events\ConnectWebhookReceived;
use Lanos\CashierConnect\Http\Controllers\WebhookController;
use Lanos\CashierConnect\Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    public function test_webhook_endpoint_rejects_unsigned_requests_when_secret_set(): void
    {
        $response = $this->postJson('/connectWebhook', ['type' => 'charge.succeeded']);

        $response->assertForbidden();
    }

    public function test_cashier_webhook_secret_alone_does_not_trigger_connect_verification(): void
    {
        // Regression: the middleware must only attach when the *connect* secret
        // is configured. A plain Cashier secret must not 403 connect webhooks.
        config()->set('cashierconnect.webhook.secret', null);
        config()->set('cashier.webhook.secret', 'whsec_cashier_secret');

        $response = $this->postJson('/connectWebhook', ['type' => 'charge.succeeded']);

        $response->assertOk();
    }

    public function test_webhook_endpoint_accepts_signed_requests(): void
    {
        $payload = json_encode(['type' => 'charge.succeeded', 'data' => ['object' => []]]);

        $response = $this->call('POST', '/connectWebhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Stripe-Signature' => $this->signPayload($payload),
        ], $payload);

        $response->assertOk();
    }

    public function test_received_event_is_dispatched_for_all_webhooks(): void
    {
        Event::fake();

        $payload = json_encode(['type' => 'some.unknown_event', 'data' => []]);

        $this->call('POST', '/connectWebhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Stripe-Signature' => $this->signPayload($payload),
        ], $payload);

        Event::assertDispatched(ConnectWebhookReceived::class, function ($event) {
            return $event->payload['type'] === 'some.unknown_event';
        });
    }

    public function test_handled_event_is_not_dispatched_for_unknown_event_types(): void
    {
        Event::fake();

        $payload = json_encode(['type' => 'some.unknown_event', 'data' => []]);

        $response = $this->call('POST', '/connectWebhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Stripe-Signature' => $this->signPayload($payload),
        ], $payload);

        $response->assertOk();
        Event::assertNotDispatched(ConnectWebhookHandled::class);
    }

    public function test_handled_event_is_dispatched_when_handler_method_exists(): void
    {
        Event::fake();

        $controller = new class extends WebhookController
        {
            public function handleChargeSucceeded(array $payload)
            {
                return $this->successMethod();
            }
        };

        $payload = json_encode(['type' => 'charge.succeeded', 'data' => []]);
        $request = Request::create('/connectWebhook', 'POST', [], [], [], [], $payload);

        $response = $controller->handleWebhook($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Webhook Handled', $response->getContent());

        Event::assertDispatched(ConnectWebhookReceived::class);
        Event::assertDispatched(ConnectWebhookHandled::class);
    }

    public function test_max_network_retries_is_set_when_handling_known_event(): void
    {
        $controller = new class extends WebhookController
        {
            public function handleChargeSucceeded(array $payload)
            {
                return $this->successMethod();
            }
        };

        $payload = json_encode(['type' => 'charge.succeeded', 'data' => []]);
        $request = Request::create('/connectWebhook', 'POST', [], [], [], [], $payload);

        $controller->handleWebhook($request);

        $this->assertSame(3, \Stripe\Stripe::getMaxNetworkRetries());
    }
}
