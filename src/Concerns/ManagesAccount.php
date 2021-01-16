<?php


namespace ExpDev07\CashierConnect\Concerns;

use ExpDev07\CashierConnect\Exceptions\AccountAlreadyExistsException;
use ExpDev07\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Cashier;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Balance;
use Stripe\Exception\ApiErrorException;

/**
 * Manages a Stripe account for the model.
 *
 * @package ExpDev07\CashierConnect\Concerns
 */
trait ManagesAccount
{

    /**
     * Retrieve the Stripe account ID.
     *
     * @return string|null
     */
    public function stripeAccountId(): ?string
    {
        return $this->stripe_account_id;
    }

    /**
     * Determine if the entity has a Stripe account ID.
     *
     * @return bool
     */
    public function hasStripeAccountId(): bool
    {
        return ! is_null($this->stripeAccountId());
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
     * Determine if the entity has a Stripe account ID and throw an exception if not.
     *
     * @return void
     * @throws AccountNotFoundException
     */
    protected function assertAccountExists(): void
    {
        if (! $this->hasStripeAccountId()) {
            throw new AccountNotFoundException('Stripe account does not exist.');
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
     * Create a Stripe account for the given model.
     *
     * @param string $type
     * @param array $options
     * @return Account
     * @throws AccountAlreadyExistsException|ApiErrorException
     */
    public function createAsStripeAccount(string $type = 'express', array $options = []): Account
    {
        // Check if one already exists.
        if ($this->hasStripeAccountId()) {
            throw new AccountAlreadyExistsException('Stripe account already exists.');
        }

        // Create Stripe account with type and email.
        $options = array_merge([
            'type' => $type,
            'email' => $this->stripeAccountEmail(),
        ], $options);
        $account = Account::create($options, $this->stripeAccountOptions());

        // Save the id.
        $this->stripe_account_id = $account->id;
        $this->save();

        return $account;
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

        return Account::update($this->stripeAccountId(), $options, $this->stripeAccountOptions());
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
        return $this->hasStripeAccountId()
            ? $this->asStripeAccount()
            : $this->createAsStripeAccount($type, $options);
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
        // Delete account if it exists.
        if ($this->hasStripeAccountId()) {
            $this->deleteStripeAccount();
        }

        // Create account and return it.
        return $this->createAsStripeAccount($type, $options);
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

        // Wipe account id reference on model.
        $this->stripe_account_id = null;
        $this->save();

        return $account;
    }

    /**
     * Gets an Stripe account link URL.
     *
     * @param $type
     * @param $options
     * @return string
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function accountLinkUrl(string $type, array $options = []): string
    {
        $this->assertAccountExists();

        $options = array_merge([
            'type' => $type,
            'account' => $this->stripeAccountId(),
        ], $options);
        return AccountLink::create($options, $this->stripeAccountOptions())->url;
    }

    /**
     * Generates a redirect response to the account link URL for Stripe.
     *
     * @param $type
     * @param $options
     * @return RedirectResponse
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function redirectToAccountLink(string $type, array $options = []): RedirectResponse
    {
        return new RedirectResponse($this->accountLinkUrl($type, $options));
    }

    /**
     * Gets an URL for Stripe account onboarding.
     *
     * @param $return_url
     * @param $refresh_url
     * @param $options
     * @return string
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function accountOnboardingUrl(string $return_url, string $refresh_url, array $options = []): string
    {
        $options = array_merge([
            'return_url' => $return_url,
            'refresh_url' => $refresh_url,
        ], $options);
        return $this->accountLinkUrl('account_onboarding', $options);
    }

    /**
     * Generates a redirect response to the account onboarding URL for Stripe.
     *
     * @param $return_url
     * @param $refresh_url
     * @param $options
     * @return RedirectResponse
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function redirectToAccountOnboarding(string $return_url, string $refresh_url, array $options= [])
    {
        return new RedirectResponse($this->accountOnboardingUrl($return_url, $refresh_url, $options));
    }

    /**
     * Gets the Stripe account dashboard login URL.
     *
     * @return string
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function accountDashboardUrl(): ?string
    {
        $this->assertAccountExists();

        // Can only create login link if details has been submitted.
        return $this->hasSubmittedAccountDetails()
            ? Account::createLoginLink($this->stripeAccountId(), [], $this->stripeAccountOptions())->url
            : null;
    }

    /**
     * Generates a redirect response to the account dashboard login for Stripe.
     *
     * @return RedirectResponse
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function redirectToAccountDashboard(): RedirectResponse
    {
        return new RedirectResponse($this->accountDashboardUrl());
    }
    
    /**
     * Retreive a Stripe Connect account's balance.
     *
     * @return Balance
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function retrieveAccountBalace(): Balance
    {
        $this->assertAccountExists();

        $options = array_merge([
            'stripe_account' => $this->stripeAccountId(),
        ], $this->stripeAccountOptions());

        return Balance::retrieve($options);
    }

    /**
     * Get the default Stripe API options for the current Billable model.
     *
     * @param array $options
     * @return array
     */
    public function stripeAccountOptions(array $options = []): array
    {
        return Cashier::stripeOptions($options);
    }

}
