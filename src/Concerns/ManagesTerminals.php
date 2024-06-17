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
     * @param $data
     * @return Location
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function addTerminalLocation($data){
        $this->assertAccountExists();
        return Location::create($data, $this->stripeAccountOptions([], true));
    }

    /**
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getTerminalLocations(){
        $this->assertAccountExists();
        return Location::all([], $this->stripeAccountOptions([], true));
    }

    /**
     * @param $data
     * @return Reader
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function registerTerminalReader($data){
        $this->assertAccountExists();
        return Reader::create($data, $this->stripeAccountOptions([], true));
    }

    /**
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getTerminalReaders(){
        $this->assertAccountExists();
        return Reader::all([], $this->stripeAccountOptions([], true));
    }

    /**
     * @param $location
     * @return ConnectionToken
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function createConnectionToken($location){
        $this->assertAccountExists();
        return ConnectionToken::create([
            "location" => $location
        ], $this->stripeAccountOptions([], true));
    }

}
