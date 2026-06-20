<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Checkout;

use NiceShoply\Common\Services\Fee\BalanceService;
use NiceShoply\Common\Services\Fee\Coupon;
use NiceShoply\Common\Services\Fee\Discount;
use NiceShoply\Common\Services\Fee\Points;
use NiceShoply\Common\Services\Fee\Shipping;
use NiceShoply\Common\Services\Fee\Subtotal;
use NiceShoply\Common\Services\Fee\Tax;
use NiceShoply\Common\Services\PluginCoordinationService;

class FeeService extends BaseService
{
    /**
     * 计算结账费用清单（核心费用 + 编排后的 orderfee 插件 + 余额抵扣）。
     *
     * @return void
     */
    public function calculate(): void
    {
        $coordination           = PluginCoordinationService::getInstance();
        $classes                = $coordination->sortFeeMethodClasses((array) $this->getFeeMethodClasses());
        $appliedOrderFeePlugins = [];

        foreach ($classes as $class) {
            $pluginCode = PluginCoordinationService::resolvePluginCodeFromClass($class);
            if ($pluginCode && $coordination->shouldSkip('orderfee', $pluginCode, $appliedOrderFeePlugins)) {
                continue;
            }

            (new $class($this->checkoutService))->addFee();

            if ($pluginCode) {
                $appliedOrderFeePlugins[] = $pluginCode;
            }
        }
    }

    /**
     * 获取结账费用 method 类列表（Hook 可追加 orderfee 插件类）。
     *
     * @return array<int, class-string>
     */
    public function getFeeMethodClasses(): array
    {
        // 费用计算顺序：小计 → 促销折扣 → 优惠券 → 积分抵现 → 税 → 运费 → 余额抵扣
        // 折扣排在税/运费之前，确保税基与「含/不含折扣」口径一致（见 system_setting('tax_base_include_discount')）。
        $classes = [
            Subtotal::class,
            Discount::class,
            Coupon::class,
            Points::class,
            Tax::class,
            Shipping::class,
            BalanceService::class,
        ];

        return (array) fire_hook_filter('service.checkout.fee.methods', $classes);
    }
}
