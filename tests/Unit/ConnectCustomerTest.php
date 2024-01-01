<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests;

use Exception;
use Lanos\CashierConnect\Billable;
use Lanos\CashierConnect\ConnectCustomer;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;

class ConnectCustomerTest extends TestCase
{
    use ConnectCustomer;
    
    /**
     * Test that the Stripe account options are returned according to the model passed.
     *
     * @return void
     * @throws Exception
     */
    public function testStripeAccountOptions()
    {
        $result = $this->stripeAccountOptions('acct_1GqjPqJ0jDXjQzKl');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('stripe_account', $result);
        $this->assertEquals('acct_1GqjPqJ0jDXjQzKl', $result['stripe_account']);
        
        $result = $this->stripeAccountOptions(new TestConnectedAccount(), [
            'api_key'        => 'sk_test_1234567890',
            'stripe_version' => '2022-11-15',
            'api_base'       => 'https://local.stripe.com',
        ]);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('stripe_account', $result);
        $this->assertEquals('sk_test_1234567890', $result['api_key']);
        $this->assertEquals('2022-11-15', $result['stripe_version']);
        $this->assertEquals('https://local.stripe.com', $result['api_base']);
        $this->assertEquals('acct_other_account', $result['stripe_account']);
    }
    
    /**
     * Test that an exception is thrown when a model is passed that does not have the Billable trait
     *
     * @throws Exception
     */
    public function testStripeAccountOptionsThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This model does not have a connect Billable trait on.');
        $this->stripeAccountOptions(new TestModel());
    }
    
    public function testAssertCustomerExists()
    {
        $this->expectException(AccountNotFoundException::class);
        $testModel = new TestModelWithoutCustomer();
        $testModel->assetCustomerExists();
    }
}

class TestModel{}

class TestModelWithoutCustomer
{
    use ConnectCustomer;
    
    public function hasCustomerRecord(): bool
    {
        return false;
    }
}

class TestConnectedAccount
{
    use Billable;
    
    public function hasStripeAccount(): bool
    {
        return true;
    }
    
    public function stripeAccountId(): ?string
    {
        return 'acct_other_account';
    }
}
