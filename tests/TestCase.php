<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests;

use Illuminate\Support\Facades\Config;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('cashierconnect.webhook.secret', 'whsec_test_secret');
    }
}
