<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectCustomer as ConnectCustomerModel;
use Lanos\CashierConnect\Tests\Fixtures\Customer;
use Lanos\CashierConnect\Tests\TestCase;

class ManageCustomerTest extends TestCase
{
    public function test_has_customer_record_false_without_mapping(): void
    {
        $customer = Customer::create(['email' => 'customer@example.com']);

        $this->assertFalse($customer->hasCustomerRecord());
    }

    public function test_assert_customer_exists_throws_without_mapping(): void
    {
        $customer = Customer::create(['email' => 'customer@example.com']);

        $this->expectException(AccountNotFoundException::class);
        $this->expectExceptionMessage('Stripe customer does not exist.');

        $customer->assetCustomerExists();
    }

    public function test_create_stripe_customer_throws_when_customer_already_exists(): void
    {
        $customer = Customer::create(['email' => 'customer@example.com']);
        $host = $this->createUserWithAccount('acct_host');

        ConnectCustomerModel::create([
            'model' => get_class($customer),
            'model_id' => $customer->id,
            'stripe_customer_id' => 'cus_existing',
            'stripe_account_id' => 'acct_host',
        ]);

        $this->expectException(AccountAlreadyExistsException::class);

        $customer->createStripeCustomer($host);
    }

    public function test_create_stripe_customer_sends_to_connected_account_and_persists_mapping(): void
    {
        $this->stripeHttp->queueResponse([
            'id' => 'cus_new',
            'object' => 'customer',
            'email' => 'customer@example.com',
        ]);

        $customer = Customer::create(['email' => 'customer@example.com']);
        $host = $this->createUserWithAccount('acct_host');

        $stripeCustomer = $customer->createStripeCustomer($host, ['email' => 'customer@example.com']);

        $this->assertSame('cus_new', $stripeCustomer->id);
        $this->assertSame('acct_host', $this->stripeHttp->lastHeader('Stripe-Account'));
        $this->assertSame('customer@example.com', $this->stripeHttp->lastParams()['email']);

        $mapping = ConnectCustomerModel::where('stripe_customer_id', 'cus_new')->first();
        $this->assertNotNull($mapping);
        $this->assertSame('acct_host', $mapping->stripe_account_id);
        $this->assertSame(get_class($customer), $mapping->model);
        $this->assertSame($customer->id, (int) $mapping->model_id);
    }

    public function test_stripe_customer_id_and_account_id_read_from_mapping(): void
    {
        $customer = Customer::create(['email' => 'customer@example.com']);

        ConnectCustomerModel::create([
            'model' => get_class($customer),
            'model_id' => $customer->id,
            'stripe_customer_id' => 'cus_mapped',
            'stripe_account_id' => 'acct_mapped',
        ]);

        $this->assertTrue($customer->hasCustomerRecord());
        $this->assertSame('cus_mapped', $customer->stripeCustomerId());
        $this->assertSame('acct_mapped', $customer->stripeAccountId());
    }
}
