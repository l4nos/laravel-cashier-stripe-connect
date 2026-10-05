<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectMapping extends Model
{

    protected $primaryKey = null;
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        "future_requirements" => 'object',
        "requirements" => 'object'
    ];

    public $timestamps = false;

    protected $table = 'stripe_connect_mappings';

    public function subscriptions(){
        return $this->hasMany(config('cashierconnect.models.connect_subscription'), 'stripe_account_id', 'stripe_account_id');
    }

}
