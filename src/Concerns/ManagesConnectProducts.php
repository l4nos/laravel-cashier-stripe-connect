<?php

namespace Lanos\CashierConnect\Concerns;

use Lanos\CashierConnect\Exceptions\AccountNotFoundException;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\Transfer;

/**
 * Manages Customers that belong to a connected account (not the platform account)
 *
 * @package Lanos\CashierConnect\Concerns
 */
trait ManagesConnectProducts
{

    /**
     * This will return all products that belong to a connected account
     * @return Collection
     * @throws ApiErrorException
     */
    public function getAllProductsForAccount(): Collection
    {
       return Product::all(null, $this->stripeAccountOptions([], true));
    }

    /**
     * This will retrieve a single product that belongs to the connected account
     * @param $id
     * @return Product
     * @throws ApiErrorException
     */
    public function getSingleConnectedProduct($id): Product
    {
       return Product::retrieve($id, $this->stripeAccountOptions([], true));
    }

    /**
     * @param $id
     * @return Collection
     * @throws ApiErrorException
     */
    public function getPricesForConnectedProduct($id): Collection
    {
        return Price::all([
            "product" => $id
        ], $this->stripeAccountOptions([], true) );
    }

    /**
     * Creates a stripe product against the connected account
     * @param $data
     * @return Product
     * @throws ApiErrorException
     */
    public function createConnectedProduct($data): Product
    {
        return Product::create($data, $this->stripeAccountOptions([], true));
    }

    /**
     * Creates a stripe product against the connected account
     * @param $id
     * @param $data
     * @return Product
     * @throws ApiErrorException
     */
    public function editConnectedProduct($id, $data): Product
    {
        return Product::update($id, $data, $this->stripeAccountOptions([], true));
    }

}
