<?php

namespace Lanos\CashierConnect\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lanos\CashierConnect\CashierConnectServiceProvider;
use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Laravel\Cashier\CashierServiceProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase as Orchestra;
use Stripe\ApiRequestor;

abstract class TestCase extends Orchestra
{
    use MockeryPHPUnitIntegration;

    protected FakeStripeHttpClient $stripeHttp;

    protected function getPackageProviders($app): array
    {
        return [
            CashierServiceProvider::class,
            CashierConnectServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('cashier.secret', 'sk_test_fake_key');
        $app['config']->set('cashierconnect.webhook.secret', 'whsec_test_secret');
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('uuid_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripeHttp = new FakeStripeHttpClient;
        ApiRequestor::setHttpClient($this->stripeHttp);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    /**
     * Create a User fixture with an existing Stripe account mapping.
     */
    protected function createUserWithAccount(string $accountId = 'acct_test123'): User
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);

        ConnectMapping::create([
            'model' => get_class($user),
            'model_id' => $user->id,
            'stripe_account_id' => $accountId,
            'type' => 'express',
            'charges_enabled' => true,
        ]);

        return $user->refresh();
    }

    /**
     * Compute a valid Stripe-Signature header value for a payload.
     */
    protected function signPayload(string $payload, ?int $timestamp = null, ?string $secret = null): string
    {
        $timestamp = $timestamp ?? time();
        $secret = $secret ?? config('cashierconnect.webhook.secret');
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return "t={$timestamp},v1={$signature}";
    }
}
