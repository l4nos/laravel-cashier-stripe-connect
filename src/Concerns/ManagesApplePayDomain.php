<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Stripe\Account;
use Stripe\ApplePayDomain;
use Stripe\Exception\ApiErrorException;

/**
 * Manages a Stripe account for the model.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesApplePayDomain
{

    public function addApplePayDomain($domain){

        return ApplePayDomain::create(['domain_name' => $domain], $this->stripeAccountOptions([], true));

    }

    public function getApplePayDomains($domain){

        return ApplePayDomain::all([], $this->stripeAccountOptions([], true));

    }

}
