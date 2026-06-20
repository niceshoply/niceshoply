<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Checkout;

use Exception;
use NiceShoply\Plugin\Repositories\PluginRepo;
use NiceShoply\Plugin\Resources\Checkout\PaymentMethodItem;

class BillingService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @throws Exception
     */
    public function getMethods(): array
    {
        $billingPlugins = PluginRepo::getInstance()->getBillingMethods();

        $methods = PaymentMethodItem::collection($billingPlugins)->jsonSerialize();

        return fire_hook_filter('service.checkout.billing.methods', $methods);
    }
}
