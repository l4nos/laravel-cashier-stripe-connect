<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectSubscription;
use Lanos\CashierConnect\Models\ConnectSubscriptionItem;
use Lanos\CashierConnect\Tests\Fixtures\Customer;
use Lanos\CashierConnect\Tests\Fixtures\PlainModel;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\TestCase;

class ManagesConnectSubscriptionsTest extends TestCase
{
    protected function subscriptionFixture(array $overrides = []): array
    {
        return array_merge([
            'id' => 'sub_test123',
            'object' => 'subscription',
            'status' => 'active',
            'customer' => 'cus_test123',
            'current_period_end' => 1893456000,
            'items' => [
                'object' => 'list',
                'data' => [
                    [
                        'id' => 'si_test123',
                        'object' => 'subscription_item',
                        'price' => ['id' => 'price_test123', 'object' => 'price', 'product' => 'prod_test123'],
                        'quantity' => 1,
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_create_direct_subscription_throws_for_model_without_connect_customer_trait(): void
    {
        $user = $this->createUserWithAccount();
        $plain = new PlainModel;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not have the connect ConnectCustomer trait');

        $user->createDirectSubscription($plain, 'price_test123');
    }

    public function test_create_direct_subscription_throws_for_customer_without_record(): void
    {
        $user = $this->createUserWithAccount();
        $customer = Customer::create(['email' => 'customer@example.com']);

        $this->expectException(AccountNotFoundException::class);

        $user->createDirectSubscription($customer, 'price_test123');
    }

    public function test_create_direct_subscription_persists_subscription_and_item(): void
    {
        $this->stripeHttp->queueResponse($this->subscriptionFixture());

        $user = $this->createUserWithAccount('acct_test123');

        $subscription = $user->createDirectSubscription('cus_test123', 'price_test123', 2);

        $this->assertSame('sub_test123', $subscription->id);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame('cus_test123', $params['customer']);
        $this->assertSame('price_test123', $params['items'][0]['price']);
        $this->assertSame(2, $params['items'][0]['quantity']);
        $this->assertSame('default_incomplete', $params['payment_behavior']);

        $record = ConnectSubscription::where('stripe_id', 'sub_test123')->first();
        $this->assertNotNull($record);
        $this->assertSame('active', $record->stripe_status);
        $this->assertSame('price_test123', $record->connected_price_id);
        $this->assertSame('cus_test123', $record->stripe_customer_id);
        $this->assertSame('acct_test123', $record->stripe_account_id);
        $this->assertSame(1893456000, \Illuminate\Support\Carbon::parse($record->ends_at)->getTimestamp());

        $item = ConnectSubscriptionItem::where('stripe_id', 'si_test123')->first();
        $this->assertNotNull($item);
        $this->assertSame($record->id, $item->connected_subscription_id);
        $this->assertSame('prod_test123', $item->connected_product);
        $this->assertSame('price_test123', $item->connected_price);
        $this->assertSame(2, (int) $item->quantity);
    }

    public function test_create_direct_subscription_applies_percentage_commission(): void
    {
        $this->stripeHttp->queueResponse($this->subscriptionFixture());

        $user = $this->createUserWithAccount();
        $user->commission_type = 'percentage';
        $user->commission_rate = 15;

        $user->createDirectSubscription('cus_test123', 'price_test123');

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(15, $params['application_fee_percent']);
        $this->assertArrayNotHasKey('application_fee_amount', $params);
    }

    public function test_create_direct_subscription_applies_flat_commission(): void
    {
        $this->stripeHttp->queueResponse($this->subscriptionFixture());

        $user = $this->createUserWithAccount();
        $user->commission_type = 'flat';
        $user->commission_rate = 500;

        $user->createDirectSubscription('cus_test123', 'price_test123');

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(500, $params['application_fee_amount']);
        $this->assertArrayNotHasKey('application_fee_percent', $params);
    }

    public function test_get_subscriptions_returns_mapping_subscriptions(): void
    {
        $user = $this->createUserWithAccount('acct_test123');

        ConnectSubscription::create([
            'name' => 'default',
            'stripe_id' => 'sub_one',
            'stripe_status' => 'active',
            'connected_price_id' => 'price_test123',
            'stripe_customer_id' => 'cus_test123',
            'stripe_account_id' => 'acct_test123',
        ]);

        $subscriptions = $user->getSubscriptions();

        $this->assertCount(1, $subscriptions);
        $this->assertSame('sub_one', $subscriptions->first()->stripe_id);
    }
}
