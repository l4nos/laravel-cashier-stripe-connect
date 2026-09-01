<?php

namespace Lanos\CashierConnect\Tests\Unit;

use Lanos\CashierConnect\Tests\Fixtures\User;
use Lanos\CashierConnect\Tests\Fixtures\UserWithoutCurrency;
use Lanos\CashierConnect\Tests\TestCase;

class EstablishTransferCurrencyTest extends TestCase
{
    public function test_provided_currency_takes_precedence(): void
    {
        $user = new User;

        $this->assertSame('EUR', $user->establishTransferCurrency('EUR'));
    }

    public function test_model_default_currency_used_when_no_currency_provided(): void
    {
        $user = new User;

        $this->assertSame('GBP', $user->establishTransferCurrency());
        $this->assertSame('GBP', $user->establishTransferCurrency(null));
    }

    public function test_config_fallback_used_when_model_has_no_default_currency(): void
    {
        $user = new UserWithoutCurrency;

        $this->assertSame('usd', $user->establishTransferCurrency());
    }

    public function test_config_fallback_respects_custom_config_value(): void
    {
        config()->set('cashierconnect.currency', 'cad');

        $user = new UserWithoutCurrency;

        $this->assertSame('cad', $user->establishTransferCurrency());
    }
}
