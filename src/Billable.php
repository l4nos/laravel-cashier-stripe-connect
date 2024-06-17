<?php

namespace Lanos\CashierConnect;

use Lanos\CashierConnect\Concerns\CanCharge;
use Lanos\CashierConnect\Concerns\ManagesAccount;
use Lanos\CashierConnect\Concerns\ManagesAccountLink;
use Lanos\CashierConnect\Concerns\ManagesApplePayDomain;
use Lanos\CashierConnect\Concerns\ManagesBalance;
use Lanos\CashierConnect\Concerns\ManagesConnectCustomer;
use Lanos\CashierConnect\Concerns\ManagesConnectProducts;
use Lanos\CashierConnect\Concerns\ManagesConnectSubscriptions;
use Lanos\CashierConnect\Concerns\ManagesPaymentLinks;
use Lanos\CashierConnect\Concerns\ManagesPerson;
use Lanos\CashierConnect\Concerns\ManagesPayout;
use Lanos\CashierConnect\Concerns\ManagesTerminals;
use Lanos\CashierConnect\Concerns\ManagesTransfer;
use Laravel\Cashier\Cashier;

/**
 * Added to models for Stripe Connect functionality.
 *
 * @package Lanos\CashierConnect
 */
trait Billable
{

    use ManagesAccount;
    use ManagesAccountLink;
    use ManagesPerson;
    use ManagesBalance;
    use ManagesTransfer;
    use ManagesPaymentLinks;
    use ManagesConnectCustomer;
    use ManagesConnectSubscriptions;
    use ManagesConnectProducts;
    use CanCharge;
    use ManagesPayout;
    use ManagesApplePayDomain;
    use ManagesTerminals;

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
        if ($sendAsAccount && $this->hasStripeAccount()) {
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

    /**
     * @param $providedCurrency
     * @return mixed|string
     */
    public function establishTransferCurrency($providedCurrency = null){

        if($providedCurrency){
            return $providedCurrency;
        }

        if($this->defaultCurrency){
            return $this->defaultCurrency;
        }

        return config('cashierconnect.currency');

    }

}
