<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Lanos\CashierConnect\Models\ConnectSubscriptionItem;

/**
 * Stands in for an application supplied replacement of the packaged
 * ConnectSubscriptionItem model, configured via
 * cashierconnect.models.connect_subscription_item.
 */
class CustomConnectSubscriptionItem extends ConnectSubscriptionItem
{
    public function marker(): string
    {
        return 'custom-subscription-item';
    }
}
