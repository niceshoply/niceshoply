<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Entities;

use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Services\CheckoutService;
use Throwable;

class ShippingEntity
{
    private array $products;

    private float $subtotal;

    private float $amount;

    private float $weight;

    private array $origAddress;

    private array $destAddress;

    private int $warehouseId = 0;

    /**
     * @return $this
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  CheckoutService  $checkoutService
     * @return ShippingEntity
     * @throws Throwable
     */
    public function setCheckoutService(CheckoutService $checkoutService): static
    {
        $this->setProducts($checkoutService->getCartList());
        $this->setSubtotal($checkoutService->getSubTotal());
        $this->setAmount($checkoutService->getAmount());
        $this->setWeight($checkoutService->getCartWeight());
        $this->setDestAddress($checkoutService->getShippingAddress());

        // Set origin address from warehouse if warehouse mode is enabled
        $origAddress = [];
        if (system_setting('warehouse_enabled', false) && $this->warehouseId) {
            $warehouse = Warehouse::query()->find($this->warehouseId);
            if ($warehouse) {
                $origAddress = $warehouse->toAddressArray();
            }
        }
        $this->setOrigAddress($origAddress);

        return $this;
    }

    /**
     * @param  array  $products
     * @return $this
     */
    public function setProducts(array $products): static
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @param  float  $subtotal
     * @return $this
     */
    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * @param  float  $amount
     * @return $this
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param  float  $weight
     * @return $this
     */
    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @param  array  $address
     * @return $this
     */
    public function setOrigAddress(array $address): static
    {
        $this->origAddress = $address;

        return $this;
    }

    /**
     * @param  array  $address
     * @return $this
     */
    public function setDestAddress(array $address): static
    {
        $this->destAddress = $address;

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return array
     */
    public function getOrigAddress(): array
    {
        return $this->origAddress;
    }

    /**
     * @return array
     */
    public function getDestAddress(): array
    {
        return $this->destAddress;
    }

    /**
     * @param  int  $warehouseId
     * @return $this
     */
    public function setWarehouseId(int $warehouseId): static
    {
        $this->warehouseId = $warehouseId;

        return $this;
    }

    /**
     * @return int
     */
    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }
}
