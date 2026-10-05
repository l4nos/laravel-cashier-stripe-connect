<?php

namespace Lanos\CashierConnect\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a model configured in cashierconnect.models does not exist or
 * does not extend the packaged model it replaces.
 *
 * @package Lanos\CashierConnect\Exceptions
 */
class InvalidModelConfigurationException extends InvalidArgumentException
{

    public static function missingClass(string $key, string $model): self
    {
        return new self("The model [$model] configured for [cashierconnect.models.$key] does not exist.");
    }

    public static function mustExtend(string $key, string $model, string $base): self
    {
        return new self("The model [$model] configured for [cashierconnect.models.$key] must extend [$base].");
    }

}
