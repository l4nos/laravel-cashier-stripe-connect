<?php

use Lanos\CashierConnect\Models\ConnectSubscriptionItem;
use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Models\ConnectSubscription;
use Lanos\CashierConnect\Models\ConnectCustomer;
use Lanos\CashierConnect\Models\TestModel;


return [

    'models' => [
        'connect_subscription_item' => ConnectSubscriptionItem::class,
        'connect_subscription' => ConnectSubscription::class,
        'connect_mapping' => ConnectMapping::class,
        'connect_customer' => ConnectCustomer::class,
        'test_model' => TestModel::class,
    ],

    'webhook' => [
        'secret' => env('CONNECT_WEBHOOK_SECRET'),
        'tolerance' => env('CONNECT_WEBHOOK_TOLERANCE', 300)
    ],

    'events' => [
        // SUBSCRIPTION ONES
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'customer.updated',
        'customer.deleted',
        'invoice.payment_action_required',
        'invoice.payment_succeeded',
        // DIRECT CHARGE PAYMENTS
        'charge.succeeded'
    ],

    /** Used when the model doesn't have a currency assigned to it or the currency isn't provided by the function */

    'currency' => env('CASHIER_CONNECT_CURRENCY', 'usd')


];