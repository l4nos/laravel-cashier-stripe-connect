<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Transfer;

/**
 * Manages balance for the Stripe connected account.
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait CanCharge
{

    /**
     * Creates a direct charge
     * @param int $amount
     * @param string|null $currencyToUse
     * @param array $options
     * @return PaymentIntent
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createDirectCharge(int $amount, string $currencyToUse = null, array $options = []): PaymentIntent
    {

        $this->assertAccountExists();

        // Create payload for the transfer.
        $options = array_merge([
            'amount' => $amount,
            'currency' => Str::lower($this->establishTransferCurrency($currencyToUse)),
        ], $options);

        // APPLY PLATFORM FEE COMMISSION - SET THIS AGAINST THE MODEL
        if (isset($this->commission_type) && isset($this->commission_rate)) {
            if ($this->commission_type === 'percentage') {
                $options['application_fee_amount'] = round($this->calculatePercentageFee($amount));
            } else {
                $options['application_fee_amount'] = round($this->commission_rate);
            }
        }


        return PaymentIntent::create($options, $this->stripeAccountOptions([],true));

    }

    /**
     * @param int $amount
     * @param string|null $currencyToUse
     * @param array $options
     * @param bool $onBehalfOf
     * @return PaymentIntent
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createDestinationCharge(int $amount, string $currencyToUse = null, array $options = [], bool $onBehalfOf = false): PaymentIntent
    {

        $this->assertAccountExists();

        // Create payload for the transfer.
        $options = array_merge([
            'amount' => $amount,
            'transfer_data' => [
              'destination' => $this->stripeAccountId()
            ],
            'currency' => Str::lower($this->establishTransferCurrency($currencyToUse)),
        ], $options);

        if($onBehalfOf){
            $options['on_behalf_of'] = $this->stripeAccountId();
        }

        // APPLY PLATFORM FEE COMMISSION - SET THIS AGAINST THE MODEL
        if (isset($this->commission_type) && isset($this->commission_rate)) {
            if ($this->commission_type === 'percentage') {
                $options['application_fee_amount'] = ceil($this->calculatePercentageFee($amount));
            } else {
                $options['application_fee_amount'] = ceil($this->commission_rate);
            }
        }

        return PaymentIntent::create($options, $this->stripeAccountOptions());

    }


    /**
     * @param $amount
     * @return float|int
     * @throws \Exception
     */
    private function calculatePercentageFee($amount){
        if($this->commission_rate < 100){
            return ($this->commission_rate / 100) * $amount;
        }else{
            throw new \Exception('You cannot charge more than 100% fee.');
        }
    }

}
