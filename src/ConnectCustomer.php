<?php


namespace Lanos\CashierConnect;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Lanos\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Lanos\CashierConnect\Models\ConnectMapping;
use Laravel\Cashier\Cashier;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

/**
 * Added to connected customer models to allow localized management of subscriptions
 *
 * @package Lanos\CashierConnect
 */
trait ConnectCustomer
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
    public function stripeAccountOptions($connectedAccount, array $options = []): array
    {

        $traits = class_uses($connectedAccount);

        if(!in_array('Billable', $traits)){
            throw new Exception('This model does not have a connect Billable trait on.');
        }

        if ($connectedAccount->hasStripeAccount()) {
            $options['stripe_account'] = $connectedAccount->stripeAccountId();
        }else{
            throw new AccountNotFoundException('This model does not have a connect Billable trait on.');
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
     * Determine if the entity has a Stripe account ID and throw an exception if not.
     *
     * @return void
     * @throws AccountNotFoundException
     */
    protected function assetCustomerExists(): void
    {
        if (! $this->hasCustomerRecord()) {
            throw new AccountNotFoundException('Stripe customer does not exist.');
        }
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

        $model::find($this->getHostIDField($connectedAccount));

        return $model;

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

}
