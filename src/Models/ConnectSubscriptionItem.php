<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Contracts\ConnectSubscriptionItemContract;

class ConnectSubscriptionItem extends Model implements ConnectSubscriptionItemContract
{

    protected $guarded = [];
    protected $table = 'connected_subscription_items';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'meter_id' => 'string',
    ];

    public function subscription()
    {
        return $this->belongsTo(config('cashierconnect.models.connect_subscription'), 'connected_subscription_id', 'id');
    }
}
