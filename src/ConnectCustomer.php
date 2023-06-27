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
trait ConnectCustomer
{

    use ManageCustomer;
    use ManageConnectedPaymentMethods;

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
    public function stripeAccountOptions($connectedAccount, array $options = []): array
    {

        if(gettype($connectedAccount) === 'string'){
            // CONNECTED ACCOUNT ID DIRECTLY PROVIDED
            $options['stripe_account'] = $connectedAccount;
        }else{

            // A MODEL PROVIDED, LOOKUP ITS ACCOUNT ID
            $traits = class_uses($connectedAccount);

            if(!in_array('Lanos\CashierConnect\Billable', $traits)){
                throw new Exception('The '.class_basename($connectedAccount).' model does not have the connect Billable trait.');
            }

            if ($connectedAccount->hasStripeAccount()) {
                $options['stripe_account'] = $connectedAccount->stripeAccountId();
            }else{
                throw new AccountNotFoundException('The '.class_basename($connectedAccount).' model does not have a Stripe Account.');
            }
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
     * Determine if the entity has a Stripe account ID and throw an exception if not.
     *
     * @return void
     * @throws AccountNotFoundException
     */
    public function assetCustomerExists(): void
    {
        if (! $this->hasCustomerRecord()) {
            throw new AccountNotFoundException('Stripe customer does not exist.');
        }
    }


}
