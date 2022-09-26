<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectSubscription extends Model
{

    protected $guarded = [];
    protected $table = 'connected_subscriptions';

    public function items(){
        return $this->hasMany(ConnectSubscriptionItem::class, 'connected_subscription_id', 'id');
    }

}
