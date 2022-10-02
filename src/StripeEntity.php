<?php


namespace Lanos\CashierConnect;


use Exception;
use Lanos\CashierConnect\Concerns\ManageConnectedPaymentMethods;
use Lanos\CashierConnect\Concerns\ManageCustomer;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Laravel\Cashier\Cashier;

/**
 * Added to connected customer models to allow localized management of subscriptions
 *
 * @package Lanos\CashierConnect
 */
trait StripeEntity
{

    /**
     * Let's say you had a connected store model and a connected customer model
     * You would create the model first as usual, then create the connected customer record in stripe
     * E.G Customer::createConnectedCustomer($storeModel);
     * The system will then create the stripe customer record against the connected account you've provided.
     *
     *
     * The above referenced function example will feed into this function, if you want to extend and write your own
     * functions you need to pass the Billable model in you want to link to.
     *
     * @param mixed $connectedAccount
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function stripeAccountOptions(array $options = [], string $sendAsAccount = ''): array
    {
        // Include Stripe Account id if present. This is so we can make requests on the behalf of the account.
        // Read more: https://stripe.com/docs/api/connected_accounts?lang=php.
        if ($sendAsAccount) {
            $options['stripe_account'] = $sendAsAccount;
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
