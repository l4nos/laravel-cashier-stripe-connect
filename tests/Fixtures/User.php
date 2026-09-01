<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Billable;

class User extends Model
{
    use Billable;

    public $defaultCurrency = 'GBP';

    protected $guarded = [];

    protected $table = 'users';
}
