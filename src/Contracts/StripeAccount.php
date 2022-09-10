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
interface StripeAccount
{

    /**
     * The model as a Stripe Account.
     *
     * @return Account
     * @throws AccountNotFoundException|ApiErrorException
     */
    function asStripeAccount(): Account;

    /**
     * The Stripe account ID.
     *
     * @return string|null
     */
    function stripeAccountId(): ?string;

    /**
     * The Stripe account email address.
     *
     * @return string
     */
    function stripeAccountEmail(): string;

    /**
     * The default Stripe API options for the current Billable model.
     *
     * @param array $options
     * @return array
     */
    function stripeAccountOptions(array $options = []): array;

}
