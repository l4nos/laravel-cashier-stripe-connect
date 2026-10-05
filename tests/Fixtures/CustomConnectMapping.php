<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Lanos\CashierConnect\Models\ConnectMapping;

/**
 * Stands in for an application supplied replacement of the packaged
 * ConnectMapping model, configured via cashierconnect.models.connect_mapping.
 */
class CustomConnectMapping extends ConnectMapping
{
    public function marker(): string
    {
        return 'custom-mapping';
    }
}
