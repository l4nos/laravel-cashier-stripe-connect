<?php


namespace ExpDev07\CashierConnect;


use ExpDev07\CashierConnect\Concerns\ManagesAccount;
use ExpDev07\CashierConnect\Concerns\ManagesAccountLink;
use ExpDev07\CashierConnect\Concerns\ManagesBalance;
use ExpDev07\CashierConnect\Concerns\ManagesPayout;
use ExpDev07\CashierConnect\Concerns\ManagesPerson;
use ExpDev07\CashierConnect\Concerns\ManagesTransfer;
use Laravel\Cashier\Cashier;

/**
 * Added to models for Stripe Connect functionality.
 *
 * @package ExpDev07\CashierConnect
 */
trait Billable
{
    use ManagesAccount;
    use ManagesAccountLink;
    use ManagesPerson;
    use ManagesBalance;
    use ManagesTransfer;
    use ManagesPayout;

    /**
     * The default Stripe API options for the current Billable model.
     *
     * @param array $options
     * @param bool $sendAsAccount
     * @return array
     */
    public function stripeAccountOptions(array $options = [], bool $sendAsAccount = false): array
    {
        // Include Stripe Account id if present. This is so we can make requests on the behalf of the account.
        // Read more: https://stripe.com/docs/api/connected_accounts?lang=php.
        if ($sendAsAccount && $this->hasStripeAccountId()) {
            $options['stripe_account'] = $this->stripeAccountId();
        }
        
        // Workaround for Cashier 12.x 
        if (version_compare(Cashier::VERSION, '12.15.0', '<=')) {
            return array_merge(Cashier::stripeOptions($options));
        }

        $stripeOptions = Cashier::stripe($options);
        
        return array_merge($options, [
            'api_key' => $stripeOptions->getApiKey()
        ]);
    }

}
