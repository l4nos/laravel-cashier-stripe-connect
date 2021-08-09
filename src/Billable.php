<?php


namespace ExpDev07\CashierConnect;


use ExpDev07\CashierConnect\Concerns\ManagesAccount;
use ExpDev07\CashierConnect\Concerns\ManagesVendor;

/**
 * Added to models for Stripe Connect functionality.
 *
 * @package ExpDev07\CashierConnect
 */
trait Billable
{
    use ManagesAccount, ManagesVendor;
}