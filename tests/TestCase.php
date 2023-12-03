<?php

declare(strict_types=1);

namespace Lanos\CashierConnect\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use CreatesApplication;
    
    protected $app;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = $this->createApplication();
    
        // Set config for testing
        $this->app['config']->set('cashierconnect.secret', 'sk_test_4eC39HqLyjWDarjtT1zdp7dc');
    }
}