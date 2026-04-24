<?php

namespace Lanos\CashierConnect\Concerns;

use Carbon\Carbon;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Payout;

/**
 * Manages payout for the Stripe connected account.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesPayout
{

    /**
     * Pay
     *
     * @param int $amount Amount to be transferred to your bank account or debit card.
     * @param string $currency Three-letter ISO currency code, in lowercase. Must be a supported currency.
     * @param array $options
     * @return Payout
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function payoutStripeAccount(int $amount, string $currency = 'USD', array $options = []): Payout
    {
        $this->assertAccountExists();

        // Create the payload for payout.
        $options = array_merge($options, [
            'amount' => $amount,
            'currency' => Str::lower($currency),
        ]);

        return Payout::create($options, $this->stripeAccountOptions([], true));
    }

}
