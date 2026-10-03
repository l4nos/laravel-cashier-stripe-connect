<?php

namespace Lanos\CashierConnect;

use Illuminate\Support\ServiceProvider;
use Lanos\CashierConnect\Console\ConnectWebhook;
use Lanos\CashierConnect\Exceptions\InvalidModelConfigurationException;
use Lanos\CashierConnect\Models\ConnectCustomer;
use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Models\ConnectSubscription;
use Lanos\CashierConnect\Models\ConnectSubscriptionItem;
use Laravel\Cashier\Cashier;

/**
 * Service provider for the package.
 *
 * @package Lanos\CashierConnect\Providers
 */
class CashierConnectServiceProvider extends ServiceProvider
{

    /**
     * The packaged model each cashierconnect.models entry must extend.
     *
     * @var array<string, class-string>
     */
    public const MODELS = [
        'connect_subscription_item' => ConnectSubscriptionItem::class,
        'connect_subscription' => ConnectSubscription::class,
        'connect_mapping' => ConnectMapping::class,
        'connect_customer' => ConnectCustomer::class,
    ];

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->initializePublishing();
        $this->initializeCommands();
        $this->setupRoutes();
        $this->setupConfig();
        $this->validateModels();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cashierconnect.php', 'cashierconnect'
        );
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function initializePublishing()
    {
        if ($this->app->runningInConsole()) {

            $publishesMigrationsMethod = method_exists($this, 'publishesMigrations')
                ? 'publishesMigrations'
                : 'publishes';

            $this->{$publishesMigrationsMethod}([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'migrations');
            $this->{$publishesMigrationsMethod}([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations/tenant'),
            ], 'tenancy-migrations');
        }
    }

    /**
     * Register the package's console commands.
     *
     * @return void
     */
    protected function initializeCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConnectWebhook::class
            ]);
        }
    }

    /**
     * Register the package's console commands.
     *
     * @return void
     */
    protected function setupRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');

    }

    /**
     * Register the package's config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cashierconnect.php' => config_path('cashierconnect.php'),
            ], 'config');
        }
    }

    /**
     * Ensure every configured model exists and extends the packaged model it
     * replaces, so a misconfiguration fails at boot with a clear message
     * rather than deep inside Eloquent.
     *
     * @return void
     *
     * @throws InvalidModelConfigurationException
     */
    public static function validateModels()
    {
        foreach (self::MODELS as $key => $base) {
            $model = config("cashierconnect.models.{$key}", $base);

            if (! is_string($model) || ! class_exists($model)) {
                throw InvalidModelConfigurationException::missingClass($key, is_string($model) ? $model : get_debug_type($model));
            }

            if (! is_a($model, $base, true)) {
                throw InvalidModelConfigurationException::mustExtend($key, $model, $base);
            }
        }
    }

}
