<?php

namespace ExpDev07\CashierConnect\Concerns;

/**
 * Manages Stripe connected accounts for the model.
 *
 * @package ExpDev07\CashierConnect\Concerns
 */
trait ManagesVendor {
    /**
     * Bump a connected account's balance
     * 
     * @param $amount, $currency, $connectedAccountStripeId
     * 
     * @return bool
     */
    public function bumpConnectedAccountBalance($amount, $currency, $connectedAccountStripeId): bool
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        \Stripe\Transfer::create([
            'amount' => $amount,
            'currency' => $currency,
            'destination' => $connectedAccountStripeId,
        ]);
        return true;
    }

    /**
     * Pay a connected account
     * 
     * @param $amount, $currency, $connectedAccountStripeId
     * 
     * @return bool
     */
    public function payConnectedAccount($amount, $currency, $connectedAccountStripeId) :bool
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        \Stripe\Payout::create([
            'amount' => $amount,
            'currency' => $currency,
        ], ['stripe_account' => $connectedAccountStripeId]);
        return true;
    }
}