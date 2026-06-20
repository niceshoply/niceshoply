<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Coupon\Services\CouponFee;
use Plugin\Coupon\Services\CouponService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 1) 把优惠券折扣作为一个 Fee 计算项注入结算费用流程
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = CouponFee::class;

            return $classes;
        });

        // 2) 后台「营销」菜单注入优惠券管理入口
        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'coupons.index',
                'title'           => __('Coupon::common.menu_title'),
                'url'             => console_route('coupons.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });

        // 3) 下单成功后核销优惠券（记录使用、累加使用次数）
        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            CouponService::getInstance()->redeemForOrder($data['order'] ?? null, $data['checkout'] ?? []);
        });
    }
}
