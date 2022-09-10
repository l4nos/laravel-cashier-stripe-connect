<?php

namespace Lanos\CashierConnect\Models;

use Lanos\CashierConnect\Billable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{

    use Billable;

    public $incrementing = false;
    public $defaultCurrency = 'GBP';

}