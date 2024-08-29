<?php

namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Terminal\ConnectionToken;
use Stripe\Terminal\Location;
use Stripe\Terminal\Reader;

/**
 * Manages terminals and locations for the connected account
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesTerminals
{

    /**
     * @param array $data
     * @param bool $direct
     * @return Location
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function addTerminalLocation(array $data, bool $direct = false): Location{
        $this->assertAccountExists();
        return Location::create($data, $this->stripeAccountOptions([], $direct));
    }

    /**
     * @param array $params
     * @param bool $direct
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getTerminalLocations(array $params, bool $direct = false): Collection{
        $this->assertAccountExists();
        return Location::all($params, $this->stripeAccountOptions([], $direct));
    }


    /**
     * @param array $data
     * @param bool $direct
     * @return Reader
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function registerTerminalReader(array $data, bool $direct = false): Reader{
        $this->assertAccountExists();
        return Reader::create($data, $this->stripeAccountOptions([], $direct));
    }

    /**
     * @param array $params
     * @param bool $direct
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getTerminalReaders(array $params = [], bool $direct = false): Collection{
        $this->assertAccountExists();
        return Reader::all($params, $this->stripeAccountOptions([], $direct));
    }

    /**
     * @param string $location
     * @param bool $direct
     * @return ConnectionToken
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createConnectionToken(array $params = [], bool $direct = false): ConnectionToken{
        $this->assertAccountExists();
        return ConnectionToken::create($params, $this->stripeAccountOptions([], $direct));
    }

}
