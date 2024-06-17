<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Stripe\Account;
use Stripe\ApplePayDomain;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;

/**
 * Manages a Stripe account for the model.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesApplePayDomain
{

    /**
     * @param $domain
     * @return ApplePayDomain
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function addApplePayDomain($domain){
        $this->assertAccountExists();
        return ApplePayDomain::create(['domain_name' => $domain], $this->stripeAccountOptions([], true));

    }

    /**
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getApplePayDomains(){
        $this->assertAccountExists();
        return ApplePayDomain::all([], $this->stripeAccountOptions([], true));
    }

}
