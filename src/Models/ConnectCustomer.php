<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectCustomer extends Model
{

    protected $primaryKey = null;
    public $incrementing = false;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'stripe_connected_customer_mappings';

}
