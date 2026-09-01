<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Contracts\ConnectCustomerContract;
use Lanos\CashierConnect\Contracts\ConnectMappingContract;
use Lanos\CashierConnect\Contracts\ConnectSubscriptionContract;
use Lanos\CashierConnect\Contracts\ConnectSubscriptionItemContract;
use Lanos\CashierConnect\Models\ConnectCustomer;
use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Models\ConnectSubscription;
use Lanos\CashierConnect\Models\ConnectSubscriptionItem;
use Lanos\CashierConnect\Tests\Fixtures\CustomConnectCustomer;
use Lanos\CashierConnect\Tests\Fixtures\CustomConnectMapping;
use Lanos\CashierConnect\Tests\Fixtures\CustomConnectSubscription;
use Lanos\CashierConnect\Tests\Fixtures\CustomConnectSubscriptionItem;
use Lanos\CashierConnect\Tests\Fixtures\Customer;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\TestCase;

/**
 * Covers the configurable model layer: every packaged model may be swapped for
 * an application supplied one through the cashierconnect.models config, and the
 * package must resolve the configured class rather than the packaged default.
 */
class CustomModelsTest extends TestCase
{
    /**
     * Config key => [packaged model, contract it must satisfy].
     *
     * @return array<string, array{0: class-string, 1: class-string}>
     */
    public static function modelConfigProvider(): array
    {
        return [
            'connect_mapping' => ['connect_mapping', ConnectMapping::class, ConnectMappingContract::class],
            'connect_customer' => ['connect_customer', ConnectCustomer::class, ConnectCustomerContract::class],
            'connect_subscription' => ['connect_subscription', ConnectSubscription::class, ConnectSubscriptionContract::class],
            'connect_subscription_item' => ['connect_subscription_item', ConnectSubscriptionItem::class, ConnectSubscriptionItemContract::class],
        ];
    }

    /**
     * @dataProvider modelConfigProvider
     */
    public function test_config_defaults_to_the_packaged_model(string $key, string $model): void
    {
        $this->assertSame($model, config("cashierconnect.models.{$key}"));
    }

    /**
     * @dataProvider modelConfigProvider
     */
    public function test_packaged_model_implements_its_contract(string $key, string $model, string $contract): void
    {
        $this->assertInstanceOf($contract, new $model);
    }

    public function test_config_only_exposes_the_documented_model_keys(): void
    {
        $this->assertSame(
            ['connect_subscription_item', 'connect_subscription', 'connect_mapping', 'connect_customer'],
            array_keys(config('cashierconnect.models'))
        );
    }

    public function test_every_configured_model_class_is_loadable(): void
    {
        foreach (config('cashierconnect.models') as $key => $model) {
            $this->assertTrue(class_exists($model), "Configured model [{$model}] for [{$key}] does not exist.");
        }
    }

    public function test_model_config_does_not_swallow_the_other_config_keys(): void
    {
        $this->assertSame('whsec_test_secret', config('cashierconnect.webhook.secret'));
        $this->assertSame(300, config('cashierconnect.webhook.tolerance'));
        $this->assertSame('usd', config('cashierconnect.currency'));
        $this->assertContains('charge.succeeded', config('cashierconnect.events'));
        $this->assertNull(config('cashierconnect.models.webhook'));
        $this->assertNull(config('cashierconnect.models.currency'));
        $this->assertNull(config('cashierconnect.models.events'));
    }

    public function test_account_mapping_relation_uses_the_configured_mapping_model(): void
    {
        $user = new User;

        $this->assertInstanceOf(ConnectMapping::class, $user->stripeAccountMapping()->getRelated());

        config()->set('cashierconnect.models.connect_mapping', CustomConnectMapping::class);

        $this->assertInstanceOf(CustomConnectMapping::class, $user->stripeAccountMapping()->getRelated());
    }

    public function test_customer_mapping_relation_uses_the_configured_customer_model(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(ConnectCustomer::class, $customer->stripeCustomerMapping()->getRelated());

        config()->set('cashierconnect.models.connect_customer', CustomConnectCustomer::class);

        $this->assertInstanceOf(CustomConnectCustomer::class, $customer->stripeCustomerMapping()->getRelated());
    }

    public function test_account_mapping_resolves_records_through_the_custom_model(): void
    {
        config()->set('cashierconnect.models.connect_mapping', CustomConnectMapping::class);

        $user = $this->createUserWithAccount('acct_custom');

        $this->assertTrue($user->hasStripeAccount());
        $this->assertInstanceOf(CustomConnectMapping::class, $user->stripeAccountMapping);
        $this->assertSame('acct_custom', $user->stripeAccountId());
    }

    public function test_mapping_subscriptions_relation_uses_the_configured_subscription_model(): void
    {
        config()->set('cashierconnect.models.connect_subscription', CustomConnectSubscription::class);

        $mapping = ConnectMapping::create([
            'model' => User::class,
            'model_id' => 1,
            'stripe_account_id' => 'acct_rel',
            'type' => 'express',
        ]);

        ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_custom',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_custom',
            'stripe_customer_id' => 'cus_custom',
            'stripe_account_id' => 'acct_rel',
        ]);

        $this->assertInstanceOf(CustomConnectSubscription::class, $mapping->subscriptions()->getRelated());
        $this->assertInstanceOf(CustomConnectSubscription::class, $mapping->subscriptions->first());
    }

    public function test_subscription_item_relations_use_the_configured_models(): void
    {
        config()->set('cashierconnect.models.connect_subscription', CustomConnectSubscription::class);
        config()->set('cashierconnect.models.connect_subscription_item', CustomConnectSubscriptionItem::class);

        $subscription = ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_items',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_items',
            'stripe_customer_id' => 'cus_items',
            'stripe_account_id' => 'acct_items',
        ]);

        $item = ConnectSubscriptionItem::create([
            'connected_subscription_id' => $subscription->id,
            'stripe_id' => 'si_items',
            'connected_product' => 'prod_items',
            'connected_price' => 'price_items',
            'quantity' => 1,
        ]);

        $this->assertInstanceOf(CustomConnectSubscriptionItem::class, $subscription->items->first());
        $this->assertInstanceOf(CustomConnectSubscription::class, $item->subscription);
    }

    public function test_create_direct_subscription_persists_through_the_configured_models(): void
    {
        config()->set('cashierconnect.models.connect_subscription', CustomConnectSubscription::class);
        config()->set('cashierconnect.models.connect_subscription_item', CustomConnectSubscriptionItem::class);

        $this->stripeHttp->queueResponse([
            'id' => 'sub_test123',
            'object' => 'subscription',
            'status' => 'active',
            'customer' => 'cus_test123',
            'items' => [
                'object' => 'list',
                'data' => [
                    [
                        'id' => 'si_test123',
                        'object' => 'subscription_item',
                        'current_period_end' => 1893456000,
                        'price' => ['id' => 'price_test123', 'object' => 'price', 'product' => 'prod_test123'],
                        'quantity' => 1,
                    ],
                ],
            ],
        ]);

        $user = $this->createUserWithAccount('acct_test123');

        $user->createDirectSubscription('cus_test123', 'price_test123');

        $subscription = CustomConnectSubscription::where('stripe_id', 'sub_test123')->first();
        $this->assertNotNull($subscription);
        $this->assertSame('custom-subscription', $subscription->marker());

        $item = CustomConnectSubscriptionItem::where('stripe_id', 'si_test123')->first();
        $this->assertNotNull($item);
        $this->assertSame('custom-subscription-item', $item->marker());
        $this->assertSame($subscription->id, $item->connected_subscription_id);
    }

    public function test_delete_stripe_customer_resolves_the_host_account_via_the_configured_mapping_model(): void
    {
        config()->set('cashierconnect.models.connect_mapping', CustomConnectMapping::class);
        config()->set('cashierconnect.models.connect_customer', CustomConnectCustomer::class);

        $host = $this->createUserWithAccount('acct_host');
        $customer = Customer::create(['email' => 'customer@example.com']);

        CustomConnectCustomer::create([
            'model' => get_class($customer),
            'model_id' => $customer->id,
            'stripe_customer_id' => 'cus_delete',
            'stripe_account_id' => 'acct_host',
        ]);

        $this->stripeHttp->queueResponse(['id' => 'cus_delete', 'object' => 'customer']);
        $this->stripeHttp->queueResponse(['id' => 'cus_delete', 'object' => 'customer', 'deleted' => true]);

        $deleted = $customer->fresh()->deleteStripeCustomer();

        $this->assertSame('cus_delete', $deleted->id);
        $this->assertSame('acct_host', $this->stripeHttp->lastHeader('Stripe-Account'));
        $this->assertSame(0, CustomConnectCustomer::where('stripe_customer_id', 'cus_delete')->count());
        $this->assertSame($host->id, $host->fresh()->id);
    }

    public function test_connected_customer_check_still_keys_off_the_trait_not_the_configured_model(): void
    {
        config()->set('cashierconnect.models.connect_customer', CustomConnectCustomer::class);

        $user = $this->createUserWithAccount();
        $customer = Customer::create(['email' => 'customer@example.com']);

        CustomConnectCustomer::create([
            'model' => get_class($customer),
            'model_id' => $customer->id,
            'stripe_customer_id' => 'cus_trait',
            'stripe_account_id' => 'acct_test123',
        ]);

        $this->stripeHttp->queueResponse([
            'id' => 'sub_trait',
            'object' => 'subscription',
            'status' => 'active',
            'customer' => 'cus_trait',
            'items' => [
                'object' => 'list',
                'data' => [
                    [
                        'id' => 'si_trait',
                        'object' => 'subscription_item',
                        'current_period_end' => 1893456000,
                        'price' => ['id' => 'price_trait', 'object' => 'price', 'product' => 'prod_trait'],
                        'quantity' => 1,
                    ],
                ],
            ],
        ]);

        $user->createDirectSubscription($customer->fresh(), 'price_trait');

        $this->assertSame('cus_trait', $this->stripeHttp->lastParams()['customer']);
    }
}
