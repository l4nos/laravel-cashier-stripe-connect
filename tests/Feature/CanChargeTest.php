<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\TestCase;

class CanChargeTest extends TestCase
{
    protected function paymentIntentFixture(array $overrides = []): array
    {
        return array_merge([
            'id' => 'pi_test123',
            'object' => 'payment_intent',
            'amount' => 10000,
            'currency' => 'gbp',
            'status' => 'requires_payment_method',
        ], $overrides);
    }

    public function test_direct_charge_throws_without_account(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $this->expectException(AccountNotFoundException::class);

        $user->createDirectCharge(10000);
    }

    public function test_direct_charge_payload_uses_model_default_currency_lowercased(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount();

        $user->createDirectCharge(10000);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(10000, $params['amount']);
        $this->assertSame('gbp', $params['currency']);
        $this->assertArrayNotHasKey('application_fee_amount', $params);
    }

    public function test_direct_charge_sends_stripe_account_header(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount('acct_charge');

        $user->createDirectCharge(10000);

        $this->assertSame('acct_charge', $this->stripeHttp->lastHeader('Stripe-Account'));
    }

    public function test_direct_charge_applies_percentage_commission(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount();
        $user->commission_type = 'percentage';
        $user->commission_rate = 10;

        $user->createDirectCharge(10000);

        $this->assertSame(1000.0, $this->stripeHttp->lastParams()['application_fee_amount']);
    }

    public function test_direct_charge_applies_flat_commission(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount();
        $user->commission_type = 'flat';
        $user->commission_rate = 250;

        $user->createDirectCharge(10000);

        $this->assertSame(250.0, $this->stripeHttp->lastParams()['application_fee_amount']);
    }

    public function test_direct_charge_rejects_commission_of_100_percent_or_more(): void
    {
        $user = $this->createUserWithAccount();
        $user->commission_type = 'percentage';
        $user->commission_rate = 100;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot charge more than 100% fee.');

        $user->createDirectCharge(10000);
    }

    public function test_destination_charge_payload_contains_transfer_destination(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount('acct_dest');

        $user->createDestinationCharge(5000, 'usd');

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(5000, $params['amount']);
        $this->assertSame('usd', $params['currency']);
        $this->assertSame('acct_dest', $params['transfer_data']['destination']);
        $this->assertArrayNotHasKey('on_behalf_of', $params);
    }

    public function test_destination_charge_sets_on_behalf_of_when_requested(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount('acct_dest');

        $user->createDestinationCharge(5000, 'usd', [], true);

        $this->assertSame('acct_dest', $this->stripeHttp->lastParams()['on_behalf_of']);
    }

    public function test_destination_charge_percentage_commission_rounds_up(): void
    {
        $this->stripeHttp->queueResponse($this->paymentIntentFixture());

        $user = $this->createUserWithAccount();
        $user->commission_type = 'percentage';
        $user->commission_rate = 3;

        // 3% of 333 = 9.99, ceil = 10
        $user->createDestinationCharge(333);

        $this->assertSame(10.0, $this->stripeHttp->lastParams()['application_fee_amount']);
    }
}
