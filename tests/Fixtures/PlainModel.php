<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class PlainModel extends Model
{
    protected $guarded = [];

    protected $table = 'users';
}
