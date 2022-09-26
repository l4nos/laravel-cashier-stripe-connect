<?php

namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Transfer;

/**
 * Manages Customers that belong to a connected account (not the platform account)
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesConnectSubscriptions
{

    // TODO CREATE SUBSCRIPTION
    // TODO CANCEL SUBSCRIPTION

    /**
     * Creates a subscription between this account model and a customer model
     * @param $customer // Any model with ConnectCustomer trait
     * @param string $paymentMethod // Payment method ID from stripe, this can be set up on frontend with setup intent
     * @return void
     */
    public function createSubscription($customer, string $paymentMethod ){



    }


}
