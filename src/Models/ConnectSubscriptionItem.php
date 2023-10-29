<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Contracts\ConnectSubscriptionContract;

class ConnectSubscriptionItem extends Model implements ConnectSubscriptionContract
{

    protected $guarded = [];
    protected $table = 'connected_subscription_items';

    public function subscription(){
        return $this->belongsTo(config('cashierconnect.models.connect_subscription'), 'connected_subscription_id', 'id');
    }

}
