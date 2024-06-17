<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;

/**
 * Manages a Stripe account for the model.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesAccount
{

    /**
     * @return mixed
     */
    public function stripeAccountMapping()
    {
        return $this->belongsTo(ConnectMapping::class, $this->primaryKey, $this->getLocalIDField())->where('model', '=', get_class($this));
    }

    /**
     * Updates and returns the updated requirements against the stripe API for the mapping
     * @return ConnectMapping
     */
    public function updateStripeStatus(){

        $account = $this->asStripeAccount();

        $onboarded = [];

        // IF ITS COMPLETED FIRST TIME
        if(!$this->stripeAccountMapping->charges_enabled && $account->charges_enabled){
            $onboarded = [
                "first_onboarding_done" => 1
            ];
        }

        $this->stripeAccountMapping()->update([
            "future_requirements" => $account->future_requirements->toArray(),
            "requirements" => $account->requirements->toArray(),
            "charges_enabled" => $account->charges_enabled
        ] + $onboarded);

        $this->refresh();

        return $this->stripeAccountMapping;
    }

    /**
     * Retrieve the Stripe account ID.
     *
     * @return string|null
     */
    public function stripeAccountId(): ?string
    {
        return $this->stripeAccountMapping->stripe_account_id;
    }

    /**
     * Determine if the entity has a Stripe account ID.
     *
     * @return bool
     */
    public function hasStripeAccount(): bool
    {
        return ($this->stripeAccountMapping()->exists());
    }

    /**
     * Gets the account email to use for Stripe.
     *
     * @return string
     */
    public function stripeAccountEmail(): string
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getModelID(): int
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Determine if the entity has a Stripe account ID and throw an exception if not.
     *
     * @return void
     * @throws AccountNotFoundException
     */
    protected function assertAccountExists(): void
    {
        if (! $this->hasStripeAccount()) {
            throw new AccountNotFoundException('Stripe account does not exist for '.class_basename(static::class).' model');
        }
    }

    /**
     * Checks if the model has submitted their details.
     *
     * @return bool
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function hasSubmittedAccountDetails(): bool
    {
        $this->assertAccountExists();

        return $this->asStripeAccount()->details_submitted;
    }

    /**
     * Checks if the model has completed on-boarding process by having submitted their details.
     *
     * @return bool
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function hasCompletedOnboarding()
    {
        return $this->hasSubmittedAccountDetails();
    }

    /**
     * Get the Stripe account for the model.
     *
     * @return Account
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function asStripeAccount(): Account
    {
        $this->assertAccountExists();

        return Account::retrieve($this->stripeAccountId(), $this->stripeAccountOptions());
    }

    /**
     * Create a Stripe account for the given model.
     *
     * @param string $type
     * @param array $options
     * @return Account
     * @throws AccountAlreadyExistsException|ApiErrorException
     */
    public function createAsStripeAccount(string $type = 'standard', array $options = []): Account
    {
        // Check if model already has a connected Stripe account.
        if ($this->hasStripeAccount()) {
            throw new AccountAlreadyExistsException('Stripe account already exists.');
        }

        // Create payload.
        $options = array_merge([
            'type' => $type,
            'email' => $this->stripeAccountEmail(),
        ], $options);

        // Create account.
        $account = Account::create($options, $this->stripeAccountOptions());

        // Save the id.
        $this->stripeAccountMapping()->create([
            "stripe_account_id" => $account->id,
            "charges_enabled" => $account->charges_enabled,
            "future_requirements" => $account->future_requirements,
            "type" => $type,
            "requirements" => $account->requirements,
            "model" => get_class($this),
            $this->getLocalIDField() => $this->{$this->primaryKey}
        ]);

        $this->save();

        return $account;
    }

    /**
     * Get the Stripe account instance for the current model or create one.
     *
     * @param string $type
     * @param array $options
     * @return Account
     * @throws AccountNotFoundException|AccountAlreadyExistsException|ApiErrorException
     */
    public function createOrGetStripeAccount(string $type = 'express', array $options = []): Account
    {
        // Return Stripe account if exists, otherwise create new.
        return $this->hasStripeAccount()
            ? $this->asStripeAccount()
            : $this->createAsStripeAccount($type, $options);
    }

    /**
     * Deletes the Stripe account for the model.
     *
     * @return Account
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function deleteStripeAccount(): Account
    {
        $this->assertAccountExists();

        // Process account delete.
        $account = $this->asStripeAccount();
        $account->delete();

        // Wipe account id reference from model.
        $this->stripeAccountMapping()->delete();

        return $account;
    }

    /**
     * Deletes the Stripe account if it exists and re-creates it.
     *
     * @param string $type
     * @param array $options
     * @return Account
     * @throws AccountNotFoundException|AccountAlreadyExistsException|ApiErrorException
     */
    public function deleteAndCreateStripeAccount(string $type = 'express', array $options = []): Account
    {
        // Delete account if it already exists.
        if ($this->hasStripeAccount()) {
            $this->deleteStripeAccount();
        }

        // Create account and return it.
        return $this->createAsStripeAccount($type, $options);
    }

    /**
     * Update the underlying Stripe account information for the model.
     *
     * @param array $options
     * @return Account
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function updateStripeAccount(array $options = []): Account
    {
        $this->assertAccountExists();

        $accountUpdate = Account::update($this->stripeAccountId(), $options, $this->stripeAccountOptions());

        // UPDATE ANY FLAGS OR REQUIREMENTS
        // TODO ADD PAYOUTS ENABLED FLAG
        $mapping = $this->stripeAccountMapping()->update([
                "future_requirements" => $accountUpdate->future_requirements->toArray(),
                "requirements" => $accountUpdate->requirements->toArray(),
                "charges_enabled" => $accountUpdate->charges_enabled
        ]);

        return $accountUpdate;
    }

    /**
     * @return string
     */
    private function getLocalIDField(){

        if($this->getIncrementing()){
            return 'model_id';
        }else{
            return 'model_uuid';
        }

    }

}
