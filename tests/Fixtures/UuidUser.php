<?php

namespace Lanos\CashierConnect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Billable;

class UuidUser extends Model
{
    use Billable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $table = 'uuid_users';
}
