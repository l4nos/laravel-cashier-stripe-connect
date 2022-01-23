<?php


namespace ExpDev07\CashierConnect\Concerns;

use ExpDev07\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Transfer;
use Stripe\TransferReversal;

/**
 * Manages transfers for the Stripe connected account.
 *
 * @package ExpDev07\CashierConnect\Concerns
 */
trait ManagesTransfer
{

    /**
     * Transfers the provided amount to the Stripe account.
     *
     * @param int $amount A positive integer in cents representing how much to payout.
     * @param string $currency Three-letter ISO currency code, in lowercase. Must be a supported currency.
     * @param array $options Any additional options.
     * @return Transfer
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function transferToStripeAccount(int $amount, string $currency = 'USD', array $options = []): Transfer
    {
        $this->assertAccountExists();

        // Create payload for the transfer.
        $options = array_merge([
            'destination' => $this->stripeAccountId(),
            'amount' => $amount,
            'currency' => Str::lower($currency),
        ], $options);

        return Transfer::create($options, $this->stripeAccountOptions());
    }

    /**
     * Reverses a transfer back to the Connect Platform. This means the Stripe account will
     *
     * @param Transfer $transfer The transfer to reverse.
     * @param bool $refundFee Whether to refund the application fee too.
     * @param int|null $amount The amount to reverse.
     * @param array $options Any additional options.
     * @return TransferReversal
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function reverseTransferFromStripeAccount(Transfer $transfer, $refundFee = false, ?int $amount = null, array $options = []): TransferReversal
    {
        $this->assertAccountExists();

        // Create payload for the transfer reversal.
        $options = array_merge([
            'amount' => $amount,
            'refund_application_fee' => $refundFee,
        ], $options);

        return Transfer::createReversal($transfer->id, $options, $this->stripeAccountOptions());
    }

}
