<?php

namespace ExpDev07\CashierConnect\Concerns;

trait ManagesVendor {
    /**
     * Bump a connected account's balance
     * 
     * @param $amount, $currency, $connectedAccountStripeId
     * 
     * @return bool
     */
    public function bumpConnectedAccBalance($amount, $currency, $connectedAccountStripeId): bool
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
    public function payConnectedAcc($amount, $currency, $connectedAccountStripeId): bool
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        \Stripe\Payout::create([
            'amount' => $amount,
            'currency' => $currency,
        ], ['stripe_account' => $connectedAccountStripeId]);
        return true;
    }
}