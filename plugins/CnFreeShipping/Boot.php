<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnFreeShipping;

use NiceShoply\Common\Entities\ShippingEntity;
use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void {}

    /**
     * 仅当订单小计达到门槛时返回免运费报价，否则不提供该方式。
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        $threshold = (float) plugin_setting('cn_free_shipping', 'threshold', 0);
        if ($threshold > 0 && $entity->getSubtotal() < $threshold) {
            return [];
        }

        $code     = $this->plugin->getCode();
        $resource = $this->pluginResource->jsonSerialize();

        return [
            [
                'type'        => 'shipping',
                'code'        => "{$code}.0",
                'name'        => $resource['name'],
                'description' => $resource['description'] ?? '',
                'icon'        => $resource['icon'] ?? '',
                'cost'        => 0,
                'cost_format' => currency_format(0),
            ],
        ];
    }

    public function getShippingFee(ShippingEntity $entity): float
    {
        return 0;
    }
}
