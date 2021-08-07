<?php

namespace ExpDev07\CashierConnect\Concerns;

trait ManageVendor {
    /**
     * Bump the vendor's balance
     * 
     * @param $amount, $currency, $connected_account_stripe_id
     * 
     * @return bool
     */
    public function bumpConnectAccBalance($amount, $currency, $connected_account_stripe_id):bool
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        \Stripe\Transfer::create([
            'amount' => $amount,
            'currency' => $currency,
            'destination' => $connected_account_stripe_id,
        ]);
        return true;
    }

    /**
     * Pay a connected account
     * 
     * @param $amount, $currency, $connected_account_stripe_id
     * 
     * @return bool
     */
    public function payConnectedAcc($amount, $currency, $connected_account_stripe_id):bool
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        \Stripe\Payout::create([
            'amount' => $amount,
            'currency' => $currency,
        ], ['stripe_account' => $connected_account_stripe_id]);
        return true;
    }
}