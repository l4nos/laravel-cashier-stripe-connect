<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Billable;

class UserWithoutCurrency extends Model
{
    use Billable;

    protected $guarded = [];

    protected $table = 'users';
}
