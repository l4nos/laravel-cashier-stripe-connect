<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\TestCase;
use Stripe\Transfer;

class ManagesTransferTest extends TestCase
{
    public function test_transfer_throws_without_account(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $this->expectException(AccountNotFoundException::class);

        $user->transferToStripeAccount(1000);
    }

    public function test_transfer_payload_contains_destination_amount_and_lowercased_currency(): void
    {
        $this->stripeHttp->queueResponse([
            'id' => 'tr_test123',
            'object' => 'transfer',
            'amount' => 1000,
            'currency' => 'gbp',
            'destination' => 'acct_test123',
        ]);

        $user = $this->createUserWithAccount('acct_test123');

        $transfer = $user->transferToStripeAccount(1000);

        $this->assertInstanceOf(Transfer::class, $transfer);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame('acct_test123', $params['destination']);
        $this->assertSame(1000, $params['amount']);
        $this->assertSame('gbp', $params['currency']);
    }

    public function test_transfer_uses_provided_currency(): void
    {
        $this->stripeHttp->queueResponse(['id' => 'tr_test123', 'object' => 'transfer']);

        $user = $this->createUserWithAccount();

        $user->transferToStripeAccount(1000, 'EUR');

        $this->assertSame('eur', $this->stripeHttp->lastParams()['currency']);
    }

    public function test_reverse_transfer_payload(): void
    {
        $this->stripeHttp->queueResponse([
            'id' => 'trr_test123',
            'object' => 'transfer_reversal',
            'amount' => 500,
            'transfer' => 'tr_test123',
        ]);

        $user = $this->createUserWithAccount();
        $transfer = new Transfer('tr_test123');

        $user->reverseTransferFromStripeAccount($transfer, true, 500);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame(500, $params['amount']);
        // stripe-php encodes booleans as strings in request params.
        $this->assertSame('true', $params['refund_application_fee']);
        $this->assertStringContainsString('tr_test123', $this->stripeHttp->lastRequest()['url']);
    }
}
