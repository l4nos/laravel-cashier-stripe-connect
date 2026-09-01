<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Contracts\ConnectCustomerContract;

class ConnectCustomer extends Model implements ConnectCustomerContract
{

    protected $primaryKey = null;
    public $incrementing = false;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'stripe_connected_customer_mappings';

}
