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
trait ManagesConnectCustomer
{

    /* TODO - Not entirely sure what needs to be done from the merchant's perspective in regards to customers. */
    /* TODO - I suspect getting one and many of their customer records and corresponding parent models, need to assess how intensive that is on database */



}
