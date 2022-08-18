<?php

namespace ExpDev07\CashierConnect\Models;

use ExpDev07\CashierConnect\Billable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{

    use Billable;

    public $incrementing = false;
    public $defaultCurrency = 'GBP';

}