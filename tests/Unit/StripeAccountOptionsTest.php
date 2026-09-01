<?php

namespace Lanos\CashierConnect\Tests\Unit;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Tests\Fixtures\Customer;
use Lanos\CashierConnect\Tests\Fixtures\PlainModel;
use Lanos\CashierConnect\Tests\TestCase;

class StripeAccountOptionsTest extends TestCase
{
    public function test_billable_options_contain_api_key_from_cashier_secret(): void
    {
        $user = $this->createUserWithAccount();

        $options = $user->stripeAccountOptions();

        $this->assertSame('sk_test_fake_key', $options['api_key']);
        $this->assertArrayNotHasKey('stripe_account', $options);
    }

    public function test_billable_options_include_stripe_account_when_sending_as_account(): void
    {
        $user = $this->createUserWithAccount('acct_abc123');

        $options = $user->stripeAccountOptions([], true);

        $this->assertSame('acct_abc123', $options['stripe_account']);
        $this->assertSame('sk_test_fake_key', $options['api_key']);
    }

    public function test_billable_options_omit_stripe_account_when_model_has_no_account(): void
    {
        $user = \Lanos\CashierConnect\Tests\Fixtures\User::create(['email' => 'noaccount@example.com']);

        $options = $user->stripeAccountOptions([], true);

        $this->assertArrayNotHasKey('stripe_account', $options);
    }

    public function test_connect_customer_options_accept_account_id_string(): void
    {
        $customer = new Customer;

        $options = $customer->stripeAccountOptions('acct_direct');

        $this->assertSame('acct_direct', $options['stripe_account']);
        $this->assertSame('sk_test_fake_key', $options['api_key']);
    }

    public function test_connect_customer_options_throw_for_model_without_billable_trait(): void
    {
        $customer = new Customer;
        $plain = new PlainModel;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not have the connect Billable trait');

        $customer->stripeAccountOptions($plain);
    }

    public function test_connect_customer_options_throw_for_billable_model_without_account(): void
    {
        $customer = new Customer;
        $user = \Lanos\CashierConnect\Tests\Fixtures\User::create(['email' => 'noaccount@example.com']);

        $this->expectException(AccountNotFoundException::class);

        $customer->stripeAccountOptions($user);
    }

    public function test_connect_customer_options_resolve_account_from_billable_model(): void
    {
        $customer = new Customer;
        $user = $this->createUserWithAccount('acct_resolved');

        $options = $customer->stripeAccountOptions($user);

        $this->assertSame('acct_resolved', $options['stripe_account']);
    }
}
