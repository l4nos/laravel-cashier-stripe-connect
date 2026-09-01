<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Models\ConnectCustomer as ConnectCustomerModel;
use Lanos\CashierConnect\Tests\Fixtures\Customer;
use Lanos\CashierConnect\Tests\TestCase;

class ManageConnectedPaymentMethodsTest extends TestCase
{
    protected function createCustomerWithRecord(string $customerId = 'cus_test123', string $accountId = 'acct_test123'): Customer
    {
        $customer = Customer::create(['email' => 'customer@example.com']);

        ConnectCustomerModel::create([
            'model' => get_class($customer),
            'model_id' => $customer->id,
            'stripe_customer_id' => $customerId,
            'stripe_account_id' => $accountId,
        ]);

        return $customer->refresh();
    }

    public function test_remove_payment_method_detaches_when_owned_by_customer(): void
    {
        $this->stripeHttp->queueResponse([
            'id' => 'pm_test123',
            'object' => 'payment_method',
            'customer' => 'cus_test123',
        ]);
        $this->stripeHttp->queueResponse([
            'id' => 'pm_test123',
            'object' => 'payment_method',
            'customer' => null,
        ]);

        $customer = $this->createCustomerWithRecord();

        $detached = $customer->removePaymentMethod('pm_test123');

        $this->assertSame('pm_test123', $detached->id);
        $this->assertCount(2, $this->stripeHttp->requests);
        $this->assertStringContainsString('pm_test123/detach', $this->stripeHttp->lastRequest()['url']);
        $this->assertSame('acct_test123', $this->stripeHttp->lastHeader('Stripe-Account'));
    }

    public function test_remove_payment_method_throws_when_not_owned_by_customer(): void
    {
        $this->stripeHttp->queueResponse([
            'id' => 'pm_test123',
            'object' => 'payment_method',
            'customer' => 'cus_somebody_else',
        ]);

        $customer = $this->createCustomerWithRecord();

        try {
            $customer->removePaymentMethod('pm_test123');
            $this->fail('Expected Exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertSame('This payment method doesn\'t belong to this customer or is invalid', $e->getMessage());
        }

        // The detach call must never have been made.
        $this->assertCount(1, $this->stripeHttp->requests);
    }
}
