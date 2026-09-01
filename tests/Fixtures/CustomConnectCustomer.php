<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Lanos\CashierConnect\Models\ConnectCustomer;

/**
 * Stands in for an application supplied replacement of the packaged
 * ConnectCustomer model, configured via cashierconnect.models.connect_customer.
 */
class CustomConnectCustomer extends ConnectCustomer
{
    public function marker(): string
    {
        return 'custom-customer';
    }
}
