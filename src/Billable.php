<?php


namespace ExpDev07\CashierConnect;


use ExpDev07\CashierConnect\Concerns\ManagesAccount;

/**
 * Added to models for Stripe Connect functionality.
 *
 * @package ExpDev07\CashierConnect
 */
trait Billable
{
    use ManagesAccount;
}