<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests;

use Lanos\CashierConnect\ConnectCustomer;

class StripePaymentTest extends TestCase
{
    use TestHelper;
    use ConnectCustomer;
    
    public function testStripePayment()
    {
        $result = $this->stripeAccountOptions('acct_1GqjPqJ0jDXjQzKl');
        $this->compatAssertIsArray($result);
        $this->compatAssertStringContainsString('acct_1GqjPqJ0jDXjQzKl', $result['stripe_account']);
    }
}
