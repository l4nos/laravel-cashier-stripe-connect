<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Tests\TestCase;

class ConnectWebhookCommandTest extends TestCase
{
    public function test_command_creates_endpoint_with_the_configured_events(): void
    {
        $this->stripeHttp->queueResponse(['id' => 'we_test123', 'object' => 'webhook_endpoint']);

        $this->artisan('connect:webhook')->assertSuccessful();

        $params = $this->stripeHttp->lastParams();

        $this->assertSame(config('cashierconnect.events'), $params['enabled_events']);
        $this->assertContains('customer.subscription.created', $params['enabled_events']);
        $this->assertContains('charge.succeeded', $params['enabled_events']);
        $this->assertContains($params['connect'], [true, 'true']);
        $this->assertStringEndsWith('/connectWebhook', $params['url']);
    }

    public function test_command_respects_custom_events_url_and_api_version(): void
    {
        config()->set('cashierconnect.events', ['account.updated']);

        $this->stripeHttp->queueResponse(['id' => 'we_custom', 'object' => 'webhook_endpoint']);

        $this->artisan('connect:webhook', [
            '--url' => 'https://example.test/hooks/connect',
            '--api-version' => '2024-06-20',
        ])->assertSuccessful();

        $params = $this->stripeHttp->lastParams();

        $this->assertSame(['account.updated'], $params['enabled_events']);
        $this->assertSame('https://example.test/hooks/connect', $params['url']);
        $this->assertSame('2024-06-20', $params['api_version']);
    }

    public function test_command_disables_the_endpoint_when_requested(): void
    {
        $this->stripeHttp->queueResponse(['id' => 'we_disabled', 'object' => 'webhook_endpoint']);
        $this->stripeHttp->queueResponse(['id' => 'we_disabled', 'object' => 'webhook_endpoint', 'disabled' => true]);

        $this->artisan('connect:webhook', ['--disabled' => true])->assertSuccessful();

        $this->assertStringEndsWith('/v1/webhook_endpoints/we_disabled', $this->stripeHttp->lastRequest()['url']);
        $this->assertContains($this->stripeHttp->lastParams()['disabled'], [true, 'true']);
    }
}
