<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Lanos\CashierConnect\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged_without_publishing(): void
    {
        $this->assertSame('usd', config('cashierconnect.currency'));
        $this->assertSame(300, config('cashierconnect.webhook.tolerance'));
        $this->assertIsArray(config('cashierconnect.events'));
        $this->assertContains('customer.subscription.created', config('cashierconnect.events'));
    }

    public function test_webhook_route_is_registered(): void
    {
        $this->assertTrue(Route::has('stripeConnect.webhook'));

        $route = Route::getRoutes()->getByName('stripeConnect.webhook');

        $this->assertContains('POST', $route->methods());
        $this->assertSame('connectWebhook', $route->uri());
    }

    public function test_connect_webhook_command_is_registered(): void
    {
        $this->assertArrayHasKey('connect:webhook', Artisan::all());
    }
}
