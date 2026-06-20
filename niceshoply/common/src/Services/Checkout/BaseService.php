<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Checkout;

use NiceShoply\Common\Services\CheckoutService;

class BaseService
{
    protected CheckoutService $checkoutService;

    /**
     * @param  CheckoutService  $checkoutService
     */
    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * @param  CheckoutService  $checkoutService
     * @return static
     */
    public static function getInstance(CheckoutService $checkoutService): static
    {
        return new static($checkoutService);
    }
}
