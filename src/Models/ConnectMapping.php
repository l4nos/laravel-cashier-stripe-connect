<?php

namespace Lanos\CashierConnect\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectMapping extends Model
{

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'stripe_connect_mappings';

}