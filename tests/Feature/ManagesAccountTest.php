<?php

namespace Lanos\CashierConnect\Tests\Feature;

use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\Fixtures\UuidUser;
use Lanos\CashierConnect\Tests\TestCase;

class ManagesAccountTest extends TestCase
{
    protected function accountFixture(array $overrides = []): array
    {
        return array_merge([
            'id' => 'acct_test123',
            'object' => 'account',
            'type' => 'express',
            'email' => 'test@example.com',
            'charges_enabled' => true,
            'details_submitted' => true,
            'future_requirements' => ['currently_due' => [], 'disabled_reason' => null],
            'requirements' => ['currently_due' => [], 'disabled_reason' => null],
        ], $overrides);
    }

    public function test_has_stripe_account_returns_false_without_mapping(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $this->assertFalse($user->hasStripeAccount());
    }

    public function test_has_stripe_account_returns_true_with_mapping(): void
    {
        $user = $this->createUserWithAccount();

        $this->assertTrue($user->hasStripeAccount());
    }

    public function test_stripe_account_id_returns_mapped_id(): void
    {
        $user = $this->createUserWithAccount('acct_mapped');

        $this->assertSame('acct_mapped', $user->stripeAccountId());
    }

    public function test_account_not_found_exception_thrown_without_mapping(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $this->expectException(AccountNotFoundException::class);
        $this->expectExceptionMessage('Stripe account does not exist for User model');

        $user->asStripeAccount();
    }

    public function test_create_as_stripe_account_throws_when_account_already_exists(): void
    {
        $user = $this->createUserWithAccount();

        $this->expectException(AccountAlreadyExistsException::class);

        $user->createAsStripeAccount();
    }

    public function test_create_as_stripe_account_sends_payload_and_persists_mapping(): void
    {
        $this->stripeHttp->queueResponse($this->accountFixture(['id' => 'acct_new', 'charges_enabled' => false]));

        $user = User::create(['name' => 'New', 'email' => 'new@example.com']);

        $account = $user->createAsStripeAccount('express', ['country' => 'GB']);

        $this->assertSame('acct_new', $account->id);

        $params = $this->stripeHttp->lastParams();
        $this->assertSame('express', $params['type']);
        $this->assertSame('new@example.com', $params['email']);
        $this->assertSame('GB', $params['country']);

        $mapping = ConnectMapping::where('stripe_account_id', 'acct_new')->first();
        $this->assertNotNull($mapping);
        $this->assertSame(get_class($user), $mapping->model);
        $this->assertSame($user->id, (int) $mapping->model_id);
        $this->assertSame('express', $mapping->type);
        $this->assertFalse((bool) $mapping->charges_enabled);
    }

    public function test_create_or_get_stripe_account_retrieves_existing(): void
    {
        $this->stripeHttp->queueResponse($this->accountFixture());

        $user = $this->createUserWithAccount('acct_test123');

        $account = $user->createOrGetStripeAccount();

        $this->assertSame('acct_test123', $account->id);
        $this->assertSame('get', $this->stripeHttp->lastRequest()['method']);
    }

    public function test_as_stripe_account_retrieves_mapped_account(): void
    {
        $this->stripeHttp->queueResponse($this->accountFixture());

        $user = $this->createUserWithAccount('acct_test123');

        $account = $user->asStripeAccount();

        $this->assertSame('acct_test123', $account->id);
        $this->assertStringContainsString('acct_test123', $this->stripeHttp->lastRequest()['url']);
    }

    public function test_delete_stripe_account_removes_mapping(): void
    {
        $this->stripeHttp->queueResponse($this->accountFixture());
        $this->stripeHttp->queueResponse(['id' => 'acct_test123', 'object' => 'account', 'deleted' => true]);

        $user = $this->createUserWithAccount('acct_test123');

        $user->deleteStripeAccount();

        $this->assertFalse($user->hasStripeAccount());
        $this->assertDatabaseMissing('stripe_connect_mappings', ['stripe_account_id' => 'acct_test123']);
    }

    public function test_update_stripe_status_marks_first_onboarding_on_first_charges_enabled(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        ConnectMapping::create([
            'model' => get_class($user),
            'model_id' => $user->id,
            'stripe_account_id' => 'acct_test123',
            'type' => 'express',
            'charges_enabled' => false,
            'first_onboarding_done' => false,
        ]);

        $this->stripeHttp->queueResponse($this->accountFixture(['charges_enabled' => true]));

        $mapping = $user->refresh()->updateStripeStatus();

        $this->assertTrue((bool) $mapping->charges_enabled);
        $this->assertSame(1, (int) $mapping->first_onboarding_done);
    }

    public function test_update_stripe_status_does_not_reflag_onboarding(): void
    {
        $user = $this->createUserWithAccount('acct_test123');

        $this->stripeHttp->queueResponse($this->accountFixture(['charges_enabled' => true]));

        $mapping = $user->updateStripeStatus();

        // charges_enabled was already true on the mapping, so no first_onboarding_done flip.
        $this->assertSame(0, (int) $mapping->first_onboarding_done);
    }

    public function test_uuid_models_use_model_uuid_field(): void
    {
        $this->stripeHttp->queueResponse($this->accountFixture(['id' => 'acct_uuid']));

        $user = UuidUser::create([
            'id' => 'b3f1c2a4-1234-4abc-9def-1234567890ab',
            'email' => 'uuid@example.com',
        ]);

        $user->createAsStripeAccount();

        $mapping = ConnectMapping::where('stripe_account_id', 'acct_uuid')->first();
        $this->assertNotNull($mapping);
        $this->assertSame('b3f1c2a4-1234-4abc-9def-1234567890ab', $mapping->model_uuid);
        $this->assertNull($mapping->model_id);
    }
}
