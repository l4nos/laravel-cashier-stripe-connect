<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\TestCase;
use Stripe\Refund;

class RefundDirectChargeTest extends TestCase
{
    protected function refundFixture(array $overrides = []): array
    {
        return array_merge([
            'id' => 're_test123',
            'object' => 'refund',
            'amount' => 10000,
            'payment_intent' => 'pi_test123',
            'status' => 'succeeded',
        ], $overrides);
    }

    public function test_refund_throws_without_account(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $this->expectException(AccountNotFoundException::class);

        $user->refundDirectCharge('pi_test123');
    }

    public function test_full_refund_sends_payment_intent_without_amount(): void
    {
        $this->stripeHttp->queueResponse($this->refundFixture());

        $user = $this->createUserWithAccount('acct_test123');

        $refund = $user->refundDirectCharge('pi_test123');

        $this->assertInstanceOf(Refund::class, $refund);
        $this->assertSame('re_test123', $refund->id);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame('pi_test123', $params['payment_intent']);
        $this->assertArrayNotHasKey('amount', $params);
    }

    public function test_refund_sends_stripe_account_header(): void
    {
        $this->stripeHttp->queueResponse($this->refundFixture());

        $user = $this->createUserWithAccount('acct_refund');

        $user->refundDirectCharge('pi_test123');

        // Direct charges live on the connected account - without this header
        // Stripe responds with "No such payment_intent".
        $this->assertSame('acct_refund', $this->stripeHttp->lastHeader('Stripe-Account'));
    }

    public function test_partial_refund_sends_amount(): void
    {
        $this->stripeHttp->queueResponse($this->refundFixture(['amount' => 2500]));

        $user = $this->createUserWithAccount();

        $user->refundDirectCharge('pi_test123', 2500);

        $this->assertSame(2500, $this->stripeHttp->lastParams()['amount']);
    }

    public function test_refund_options_are_passed_through(): void
    {
        $this->stripeHttp->queueResponse($this->refundFixture());

        $user = $this->createUserWithAccount();

        $user->refundDirectCharge('pi_test123', null, [
            'reason' => 'requested_by_customer',
            'refund_application_fee' => true,
            'metadata' => ['booking_id' => '42'],
        ]);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame('requested_by_customer', $params['reason']);
        $this->assertSame('true', $params['refund_application_fee']);
        $this->assertSame('42', $params['metadata']['booking_id']);
    }

    public function test_amount_does_not_clobber_explicit_options(): void
    {
        $this->stripeHttp->queueResponse($this->refundFixture());

        $user = $this->createUserWithAccount();

        $user->refundDirectCharge('pi_test123', 1000, ['reason' => 'duplicate']);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(1000, $params['amount']);
        $this->assertSame('duplicate', $params['reason']);
    }
}
