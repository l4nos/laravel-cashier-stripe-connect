<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\ConnectCustomer;

class Customer extends Model
{
    use ConnectCustomer;

    protected $guarded = [];

    protected $table = 'customers';
}
