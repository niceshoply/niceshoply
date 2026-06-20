<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StorePickup;

use NiceShoply\Common\Entities\ShippingEntity;
use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\StorePickup\Models\PickupStore;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 后台「设置」菜单注入自提门店管理入口
        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'store_pickup.index',
                'title'           => __('StorePickup::common.menu'),
                'url'             => console_route('store_pickup.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }

    /**
     * 每个启用门店作为一个 0 运费自提选项。
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        $code     = $this->plugin->getCode();
        $resource = $this->pluginResource->jsonSerialize();

        $stores = PickupStore::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $quotes = [];
        foreach ($stores as $store) {
            $desc = trim(implode(' · ', array_filter([
                $store->address,
                $store->business_hours,
                $store->phone,
            ])));

            $quotes[] = [
                'type'        => 'shipping',
                'code'        => "{$code}.{$store->id}",
                'name'        => __('StorePickup::common.pickup_prefix').$store->name,
                'description' => $desc ?: ($resource['description'] ?? ''),
                'icon'        => $resource['icon'] ?? '',
                'cost'        => 0,
                'cost_format' => currency_format(0),
            ];
        }

        return $quotes;
    }

    public function getShippingFee(ShippingEntity $entity): float
    {
        return 0;
    }
}
