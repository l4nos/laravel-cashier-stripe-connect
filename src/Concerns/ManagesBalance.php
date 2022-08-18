<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Stripe\Balance;
use Stripe\Exception\ApiErrorException;

/**
 * Manages balance for the Stripe connected account.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesBalance
{

    /**
     * Retrieve a Stripe Connect account's balance.
     *
     * @return Balance
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function retrieveAccountBalance(): Balance
    {
        $this->assertAccountExists();

        // Create the payload for retrieving balance.
        $options = array_merge([
            'stripe_account' => $this->stripeAccountId(),
        ], $this->stripeAccountOptions());

        return Balance::retrieve($options);
    }

}
