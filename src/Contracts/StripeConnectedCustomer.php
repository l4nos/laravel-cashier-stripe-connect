<?php


namespace Lanos\CashierConnect\Contracts;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;

/**
 * Stripe account.
 *
 * @package Lanos\CashierConnect\Contracts
 */
interface StripeConnectedCustomer
{

    /**
     * The Stripe account ID.
     *
     * @return string|null
     */
    function stripeCustomerId(): ?string;

    /**
     * The default Stripe API options for the current Billable model.
     *
     * @param array $options
     * @return array
     */
    function stripeAccountOptions(array $options = []): array;

}
