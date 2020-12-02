<?php

namespace ExpDev07\CashierConnect;


use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

/**
 * Service provider for the package.
 *
 * @package ExpDev07\CashierConnect\Providers
 */
class CashierConnectServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Cashier::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'cashier-connect-migrations');
        }
    }

}
