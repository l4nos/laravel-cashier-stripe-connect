<?php


namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
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
    public function createDirectCharge(int $amount, ?string $currencyToUse = null, array $options = []): PaymentIntent
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
    public function createDestinationCharge(int $amount, ?string $currencyToUse = null, array $options = [], bool $onBehalfOf = false): PaymentIntent
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
     * Refunds a direct charge made on the connected account.
     *
     * Direct charges live on the connected account, so the refund must be
     * sent with the Stripe-Account header - standard Cashier refunds cannot
     * be used for these. Destination charge refunds should be handled via
     * standard Cashier instead.
     *
     * @param string $paymentIntent The payment intent ID (pi_...) of the direct charge.
     * @param int|null $amount Amount in the smallest currency unit to refund, or null for a full refund.
     * @param array $options Additional Stripe refund options e.g. reason, refund_application_fee, metadata.
     * @return Refund
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function refundDirectCharge(string $paymentIntent, ?int $amount = null, array $options = []): Refund
    {
        $this->assertAccountExists();

        if ($amount !== null) {
            $options['amount'] = $amount;
        }

        return Refund::create(
            ['payment_intent' => $paymentIntent] + $options,
            $this->stripeAccountOptions([], true)
        );
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
