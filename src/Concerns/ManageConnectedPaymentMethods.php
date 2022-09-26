<?php

namespace Lanos\CashierConnect\Concerns;

use Exception;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;

trait ManageConnectedPaymentMethods
{

    // NOTE: Setup intents are not the recommended way for connected accounts, you should either save the payment for later after taking a payment
    // or set it up using subscriptions and use the returned payment intent api from the first invoice on your frontend element.
    // For that reason I have no included setup intents in here, you can always write the custom method against the customer model if needed.

    /**
     * Returns a list of payment methods for
     * @return Collection
     * @throws ApiErrorException
     */
    public function getPaymentMethods(){
        $this->assetCustomerExists();
        return Customer::allPaymentMethods($this->stripeCustomerId(), $this->stripeAccountOptions($this->stripeAccountId()));
    }

    /**
     * Detaches the payment method from the customer
     * @param $id
     * @return PaymentMethod
     * @throws ApiErrorException
     * @throws Exception
     */
    public function removePaymentMethod($id){
        $this->assetCustomerExists();

        $method = PaymentMethod::retrieve($id, $this->stripeAccountOptions($this->stripeAccountId()));

        if(!$method->customer === $this->stripeAccountId()){
            throw new Exception('This payment method doesn\'t belong to this customer or is invalid');
        }

        return PaymentMethod::detach($id, $this->stripeAccountOptions($this->stripeAccountId()));

    }

}