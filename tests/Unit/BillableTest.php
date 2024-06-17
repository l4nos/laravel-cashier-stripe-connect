<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests\Unit;

use Lanos\CashierConnect\Billable;
use Lanos\CashierConnect\Tests\TestCase;

class BillableTest extends TestCase
{
    use Billable;
    
    public $defaultCurrency = 'gbp';
    
    /**
     * Test that the Stripe account options are returned.
     * @return void
     */
    public function testStripeAccountOptions()
    {
        $result = $this->stripeAccountOptions([
            'stripe_account' => 'acct_1GqjPqJ0jDXjQzKl',
        ], true);
        
        $this->assertArrayHasKey('stripe_account', $result);
        $this->assertSame('acct_1GqjPqJ0jDXjQzKl', $result['stripe_account']);
        // TODO test workarounds for Cashier 12.x
    }
    
    /**
     * Test that the default currency is returned if no Stripe account is set.
     * @return void
     */
    public function testEstablishTransformedStripeAccount()
    {
        $currency = 'mxn';
        $result = $this->establishTransferCurrency($currency);
        $this->assertSame($currency, $result);
        
        $result = $this->establishTransferCurrency();
        $this->assertSame('gbp', $result);
        
        $this->defaultCurrency = null;
        config(['cashierconnect.currency' => 'usd']);
        $result = $this->establishTransferCurrency();
        $this->assertSame('usd', $result);
    }
    
    public function hasStripeAccount(): bool
    {
        return true;
    }
    
    public function stripeAccountId(): string
    {
        return 'acct_1GqjPqJ0jDXjQzKl';
    }
}
