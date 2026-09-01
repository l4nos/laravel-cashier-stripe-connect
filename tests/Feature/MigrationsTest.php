<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Lanos\CashierConnect\Tests\TestCase;

class MigrationsTest extends TestCase
{
    public function test_stripe_connect_mappings_table_schema(): void
    {
        $this->assertTrue(Schema::hasTable('stripe_connect_mappings'));

        $this->assertTrue(Schema::hasColumns('stripe_connect_mappings', [
            'model',
            'model_id',
            'model_uuid',
            'stripe_account_id',
            'future_requirements',
            'charges_enabled',
            'first_onboarding_done',
            'requirements',
            'type',
        ]));
    }

    public function test_connected_customer_mappings_table_schema(): void
    {
        $this->assertTrue(Schema::hasTable('stripe_connected_customer_mappings'));

        $this->assertTrue(Schema::hasColumns('stripe_connected_customer_mappings', [
            'model',
            'model_id',
            'model_uuid',
            'stripe_customer_id',
            'stripe_account_id',
        ]));
    }

    public function test_connected_subscriptions_table_schema(): void
    {
        $this->assertTrue(Schema::hasTable('connected_subscriptions'));

        $this->assertTrue(Schema::hasColumns('connected_subscriptions', [
            'id',
            'name',
            'stripe_id',
            'stripe_status',
            'connected_price_id',
            'quantity',
            'trial_ends_at',
            'ends_at',
            'stripe_customer_id',
            'stripe_account_id',
        ]));
    }

    public function test_connected_subscription_items_table_schema(): void
    {
        $this->assertTrue(Schema::hasTable('connected_subscription_items'));

        $this->assertTrue(Schema::hasColumns('connected_subscription_items', [
            'id',
            'connected_subscription_id',
            'stripe_id',
            'connected_product',
            'connected_price',
            'quantity',
            'meter_event_name',
            'meter_id',
        ]));
    }
}
