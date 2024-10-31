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
        $this->assertAccountExists();
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
        $this->assertAccountExists();
        return Product::retrieve($id, $this->stripeAccountOptions([], true));
    }

    /**
     * Creates a stripe product against the connected account
     * @param $data
     * @return Product
     * @throws ApiErrorException
     */
    public function createConnectedProduct($data): Product
    {
        $this->assertAccountExists();
        return Product::create($data, $this->stripeAccountOptions([], true));
    }

    /**
     * Edits a stripe product against the connected account
     * @param $id
     * @param $data
     * @return Product
     * @throws ApiErrorException
     */
    public function editConnectedProduct($id, $data): Product
    {
        $this->assertAccountExists();
        return Product::update($id, $data, $this->stripeAccountOptions([], true));
    }

    /**
     * @param $id
     * @return Collection
     * @throws ApiErrorException
     */
    public function getPricesForConnectedProduct($id): Collection
    {
        $this->assertAccountExists();
        return Price::all([
            "product" => $id
        ], $this->stripeAccountOptions([], true) );
    }

    /**
     * Creates a price for a product on a connected account
     * @param $id
     * @param $data
     * @return Price
     * @throws ApiErrorException
     * @throws AccountNotFoundException
     */
    public function createPriceForConnectedProduct($id, $data): Price
    {
        $this->assertAccountExists();
        return Price::create($data + [
            "product" => $id
        ], $this->stripeAccountOptions([], true) );
    }

    /**
     * Gets single price against a product against a connected account
     * @param $id
     * @return Price
     * @throws ApiErrorException
     * @throws AccountNotFoundException
     */
    public function getSingleConnectedPrice($id): Price
    {
        $this->assertAccountExists();
        return Price::retrieve($id, $this->stripeAccountOptions([], true) );
    }

    /**
     * Edits a stripe price against the connected account
     * @param $id
     * @param $data
     * @return Price
     * @throws ApiErrorException
     * @throws AccountNotFoundException
     */
    public function editConnectedPrice($id, $data): Price
    {
        $this->assertAccountExists();
        return Price::update($id, $data, $this->stripeAccountOptions([], true));
    }

}
