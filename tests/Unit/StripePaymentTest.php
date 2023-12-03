<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests;

use Lanos\CashierConnect\Billable;
use Lanos\CashierConnect\ConnectCustomer;

class StripePaymentTest extends TestCase
{
    use ConnectCustomer;
    
    public function testStripePayment()
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
