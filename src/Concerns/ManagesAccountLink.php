<?php


namespace ExpDev07\CashierConnect\Concerns;

use ExpDev07\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Http\RedirectResponse;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\ApiErrorException;

/**
 * Manages links for the Stripe connected account.
 *
 * @package ExpDev07\CashierConnect\Concerns
 */
trait ManagesAccountLink
{

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
     * @param $options
     * @return string
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function accountDashboardUrl(array $options = []): ?string
    {
        $this->assertAccountExists();

        // Can only create login link if details has been submitted.
        return $this->hasSubmittedAccountDetails()
            ? Account::createLoginLink($this->stripeAccountId(), $options, $this->stripeAccountOptions())->url
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

}
