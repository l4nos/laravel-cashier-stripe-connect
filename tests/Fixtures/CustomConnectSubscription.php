<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Lanos\CashierConnect\Models\ConnectSubscription;

/**
 * Stands in for an application supplied replacement of the packaged
 * ConnectSubscription model, configured via
 * cashierconnect.models.connect_subscription.
 */
class CustomConnectSubscription extends ConnectSubscription
{
    public function marker(): string
    {
        return 'custom-subscription';
    }
}
