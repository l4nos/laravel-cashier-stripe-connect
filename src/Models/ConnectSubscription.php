<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\StripeEntity;
use Lanos\CashierConnect\Contracts\ConnectSubscriptionContract;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;

class ConnectSubscription extends Model implements ConnectSubscriptionContract
{

    use StripeEntity;

    protected $guarded = [];
    protected $table = 'connected_subscriptions';

    public function items(){
        return $this->hasMany(config('cashierconnect.models.connect_subscription_item'), 'connected_subscription_id', 'id');
    }

    /**
     * Gets the stripe subscription for the model
     * @return Subscription
     * @throws ApiErrorException
     */
    public function asStripeSubscription(){
        return Subscription::retrieve($this->stripe_id, $this->stripeAccountOptions([], $this->stripe_account_id));
    }

}
