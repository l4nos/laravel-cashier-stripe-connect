<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests\Unit;

use Lanos\CashierConnect\Billable;
use Lanos\CashierConnect\Tests\TestCase;

class BillableTest extends TestCase
{
    use Billable;
    
    public $defaultCurrency = 'gbp';
    
    public function testStripeAccountOptions()
    {
        $result = $this->stripeAccountOptions([
            'stripe_account' => 'acct_1GqjPqJ0jDXjQzKl',
        ], true);
        
        $this->assertArrayHasKey('stripe_account', $result);
        // TODO test workarounds for Cashier 12.x
    }
    
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