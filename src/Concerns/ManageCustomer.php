<?php

namespace Lanos\CashierConnect\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Models\ConnectCustomer;
use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

trait ManageCustomer
{

    /**
     * @return mixed
     */
    public function stripeCustomerMapping()
    {
        return $this->belongsTo(ConnectCustomer::class, $this->primaryKey, $this->getLocalIDField())->where('model', '=', get_class($this));
    }

    /**
     * Retrieve the Stripe account ID.
     *
     * @return string|null
     */
    public function stripeAccountId(): ?string
    {
        return $this->stripeCustomerMapping->stripe_account_id;
    }

    /**
     * Retrieve the Stripe customer ID.
     *
     * @return string|null
     */
    public function stripeCustomerId(): ?string
    {
        return $this->stripeCustomerMapping->stripe_customer_id;
    }

    /**
     * Checks if the model exists as a stripe customer
     * @return mixed
     */
    public function hasCustomerRecord(){
        return ($this->stripeCustomerMapping()->exists());
    }

    /**
     * Creates a customer against a connected account, the first parameter must be a model that has
     * the billable trait and also exists as a stripe connected account
     * @param $connectedAccount
     * @param array $customerData
     * @return Customer
     * @throws AccountAlreadyExistsException
     * @throws AccountNotFoundException
     */
    public function createStripeCustomer($connectedAccount, array $customerData = []){

        // Check if model already has a connected Stripe account.
        if ($this->hasCustomerRecord()) {
            throw new AccountAlreadyExistsException('Customer account already exists.');
        }

        $customer = Customer::create($customerData, $this->stripeAccountOptions($connectedAccount));

        // Save the id.
        $this->stripeCustomerMapping()->create([
            "stripe_customer_id" => $customer->id,
            "stripe_account_id" => $connectedAccount->stripeAccountId(),
            "model" => get_class($this),
            $this->getLocalIDField() => $this->{$this->primaryKey}
        ]);

        $this->save();

        return $customer;

    }

    /**
     * Returns the parent model that the customer belongs to
     * You should really be relating these yourself using foreign indexes and eloquent relationships
     * This is only done this way for the purposes of the plugin and the dynamic mapping
     * @return Model
     */
    private function retrieveHostConnectedAccount(): Model{

        $connectedAccount = ConnectMapping::where([
            ['stripe_account_id', '=', $this->stripeAccountId()]
        ])->first();

        $model = $connectedAccount->model;

        $modelId = $this->getHostIDField($connectedAccount);

        return $model::find($connectedAccount->$modelId);

    }

    /**
     * Deletes the Stripe customer for the model.
     *
     * @return Customer
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function deleteStripeCustomer(): Customer
    {
        $this->assetCustomerExists();

        // Process account delete.
        $customer = Customer::retrieve($this->stripeCustomerId(), $this->stripeAccountOptions($this->retrieveHostConnectedAccount()));
        $customer->delete();

        // Wipe account id reference from model.
        $this->stripeCustomerMapping()->delete();

        return $customer;
    }

    /**
     * Provides support for UUID based models
     * @return string
     */
    private function getLocalIDField(){

        if($this->incrementing){
            return 'model_id';
        }else{
            return 'model_uuid';
        }

    }

    /**
     * Provides support for UUID based models
     * @return string
     */
    private function getHostIDField(ConnectMapping $connectedAccount){

        if($connectedAccount->model_id){
            return 'model_id';
        }else{
            return 'model_uuid';
        }

    }

}
