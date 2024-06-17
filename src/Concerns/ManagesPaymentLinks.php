<?php

namespace Lanos\CashierConnect\Concerns;

use Illuminate\Support\Str;
use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentLink;
use Stripe\Terminal\ConnectionToken;
use Stripe\Terminal\Location;
use Stripe\Terminal\Reader;

/**
 * Manages terminals and locations for the connected account
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesPaymentLinks
{

    /**
     * @param $data
     * @return PaymentLink
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createDirectPaymentLink($lineItems, $options): PaymentLink{

        $options = array_merge([
            'line_items' => $lineItems
        ], $options);

        // APPLY PLATFORM FEE COMMISSION - SET THIS AGAINST THE MODEL
        if (isset($this->commission_type) && isset($this->commission_rate)) {
            if ($this->commission_type === 'percentage') {
                $options['application_fee_percent'] = round($this->commission_rate,2);
            } else {
                $options['application_fee_amount'] = round($this->commission_rate ,2);
            }
        }

        $this->assertAccountExists();
        return PaymentLink::create($options, $this->stripeAccountOptions([], true));
    }

    /**
     * @return PaymentLink
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createDestinationPaymentLink(array $lineItems, array $options, bool $onBehalfOf = false): PaymentLink{

        $options = array_merge([
            'line_items' => $lineItems,
            'transfer_data' => [
                'destination' => $this->stripeAccountId()
            ],
        ], $options);

        if($onBehalfOf){
            $options['on_behalf_of'] = $this->stripeAccountId();
        }

        // APPLY PLATFORM FEE COMMISSION - SET THIS AGAINST THE MODEL
        if (isset($this->commission_type) && isset($this->commission_rate)) {
            if ($this->commission_type === 'percentage') {
                $options['application_fee_percent'] = round($this->commission_rate,2);
            } else {
                $options['application_fee_amount'] = round($this->commission_rate ,2);
            }
        }

        $this->assertAccountExists();
        return PaymentLink::create($options, $this->stripeAccountOptions([], false));
    }

    /**
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getAllDirectPaymentLinks(): Collection{
        $this->assertAccountExists();
        return PaymentLink::all([], $this->stripeAccountOptions([], true));
    }

    // NOTE, GETTING ALL THE DESTINATION PAYMENT LINKS IS IMPRACTICAL,
    // WE CANNOT FILTER BY CONNECTED ACCOUNT ON THE STRIPE API DIRECTLY,
    // I'VE REACHED OUT TO STRIPE TO ASK THEM TO ADD THAT IN

    /**
     * @return PaymentLink
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getSingleDestinationPaymentLink($id): PaymentLink{
        $this->assertAccountExists();
        return PaymentLink::retrieve($id, $this->stripeAccountOptions([], false));
    }

    /**
     * @param $id
     * @param $data
     * @return PaymentLink
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function updateDirectPaymentLink($id, $data): PaymentLink{
        $this->assertAccountExists();
        return PaymentLink::update($id, $data, $this->stripeAccountOptions([], true));
    }

    /**
     * @param $id
     * @param $data
     * @return PaymentLink
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function updateDestinationPaymentLink($id, $data): PaymentLink{
        $this->assertAccountExists();
        return PaymentLink::update($id, $data, $this->stripeAccountOptions([], false));
    }

}
