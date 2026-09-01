<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Models\ConnectSubscription;
use Lanos\CashierConnect\Models\ConnectSubscriptionItem;
use Lanos\CashierConnect\Tests\TestCase;

class ConnectModelsTest extends TestCase
{
    public function test_connect_mapping_has_many_subscriptions(): void
    {
        $mapping = ConnectMapping::create([
            'model' => 'App\\Models\\User',
            'model_id' => 1,
            'stripe_account_id' => 'acct_rel',
            'type' => 'express',
        ]);

        ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_a',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_a',
            'stripe_customer_id' => 'cus_a',
            'stripe_account_id' => 'acct_rel',
        ]);

        ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_b',
            'stripe_status' => 'canceled',
            'connected_price_id' => 'price_b',
            'stripe_customer_id' => 'cus_b',
            'stripe_account_id' => 'acct_rel',
        ]);

        // Different account, should not be related.
        ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_c',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_c',
            'stripe_customer_id' => 'cus_c',
            'stripe_account_id' => 'acct_other',
        ]);

        $this->assertCount(2, $mapping->subscriptions);
    }

    public function test_connect_mapping_casts_requirements_to_object(): void
    {
        $mapping = ConnectMapping::create([
            'model' => 'App\\Models\\User',
            'model_id' => 1,
            'stripe_account_id' => 'acct_casts',
            'type' => 'express',
            'requirements' => ['currently_due' => ['external_account']],
            'future_requirements' => ['currently_due' => []],
        ]);

        $fresh = ConnectMapping::where('stripe_account_id', 'acct_casts')->first();

        $this->assertIsObject($fresh->requirements);
        $this->assertSame(['external_account'], $fresh->requirements->currently_due);
        $this->assertIsObject($fresh->future_requirements);
    }

    public function test_subscription_has_many_items_and_item_belongs_to_subscription(): void
    {
        $subscription = ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_rel',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_rel',
            'stripe_customer_id' => 'cus_rel',
            'stripe_account_id' => 'acct_rel',
        ]);

        $item = ConnectSubscriptionItem::create([
            'connected_subscription_id' => $subscription->id,
            'stripe_id' => 'si_rel',
            'connected_product' => 'prod_rel',
            'connected_price' => 'price_rel',
            'quantity' => 3,
        ]);

        $this->assertCount(1, $subscription->items);
        $this->assertSame('si_rel', $subscription->items->first()->stripe_id);
        $this->assertSame($subscription->id, $item->subscription->id);
    }

    public function test_subscription_item_meter_columns_are_cast(): void
    {
        $subscription = ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_meter',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_meter',
            'stripe_customer_id' => 'cus_meter',
            'stripe_account_id' => 'acct_meter',
        ]);

        $item = ConnectSubscriptionItem::create([
            'connected_subscription_id' => $subscription->id,
            'stripe_id' => 'si_meter',
            'connected_product' => 'prod_meter',
            'connected_price' => 'price_meter',
            'quantity' => 1,
            'meter_event_name' => 'api_requests',
            'meter_id' => 'mtr_test123',
        ]);

        $fresh = ConnectSubscriptionItem::find($item->id);

        $this->assertSame('api_requests', $fresh->meter_event_name);
        $this->assertSame('mtr_test123', $fresh->meter_id);
        $this->assertIsString($fresh->meter_id);
    }
}
