<?php

namespace Lanos\CashierConnect\Concerns;

use Exception;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use Stripe\Transfer;

/**
 * Manages Customers that belong to a connected account (not the platform account)
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesConnectSubscriptions
{

    /**
     * Creates a subscription between this account model and a customer model
     * It will also return the first payment intent which should be used to collect payment details and do 3DS on frontend
     * @param mixed $customer // Any model with ConnectCustomer trait
     * @param string $paymentMethod // Payment method ID from stripe, this can be set up on frontend with setup intent
     * @return Subscription
     */

    public function createDirectSubscription($customer, $price, $data = [])
    {

        return Subscription::create(
            $data + [
                "customer" => $this->determineCustomerInput($customer),
                "expand" => "latest_invoice.payment_intent",
                "transfer_data" => ['destination' => $this->stripeAccountId()],
                "items" => [['price' => $price]]
            ], $this->stripeAccountOptions([], true));

    }

    /**
     * Retrieves a subscription object by its stripe subscription ID
     * @param $id
     * @return Subscription
     * @throws ApiErrorException
     */
    public function retrieveSubscription($id): Subscription
    {
        return Subscription::retrieve($id, $this->stripeAccountOptions([], true));
    }

    private function determineCustomerInput($customer)
    {
        if (gettype($customer) === 'string') {
            return $customer;
        } else {
            return $this->handleConnectedCustomer($customer);
        }
    }

    private function handleConnectedCustomer($customer)
    {
        // IT IS A CUSTOMER TRAIT MODEL
        $traits = class_uses($customer);

        if (!in_array('ConnectCustomer', $traits)) {
            throw new Exception('This model does not have a connect ConnectCustomer trait on.');
        }

        $this->assetCustomerExists();

        return $customer->stripeCustomerId();
    }


}
