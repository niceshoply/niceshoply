<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Services\Fee\BalanceService;
use NiceShoply\Common\Services\Fee\Shipping;
use NiceShoply\Common\Services\Fee\Subtotal;
use NiceShoply\Common\Services\Fee\Tax;
use NiceShoply\Common\Services\PluginCoordinationService;
use NiceShoply\Plugin\Models\Plugin;
use Tests\TestCase;

/**
 * 插件编排结账/价格链路集成测试（IMP-04）
 */
class PluginCoordinationCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * orderfee 插件 fee class 应按 sort_order 排在核心费用之后、BalanceService 之前。
     */
    public function test_sort_fee_method_classes_respects_orderfee_sort_order(): void
    {
        Plugin::query()->updateOrCreate(
            ['code' => 'CouponDiscount'],
            ['type' => 'orderfee', 'priority' => 0]
        );
        Plugin::query()->updateOrCreate(
            ['code' => 'OrderDiscount'],
            ['type' => 'orderfee', 'priority' => 0]
        );

        PluginCoordinationService::getInstance()->updateConfig('orderfee', [
            'sort_order'     => ['OrderDiscount', 'CouponDiscount'],
            'exclusive_mode' => 'all_stack',
        ]);

        $sorted = PluginCoordinationService::getInstance()->sortFeeMethodClasses([
            Subtotal::class,
            Tax::class,
            Shipping::class,
            'Plugin\\CouponDiscount\\Services\\OrderFee',
            'Plugin\\OrderDiscount\\Services\\OrderFee',
            BalanceService::class,
        ]);

        $this->assertSame([
            Subtotal::class,
            Tax::class,
            Shipping::class,
            'Plugin\\OrderDiscount\\Services\\OrderFee',
            'Plugin\\CouponDiscount\\Services\\OrderFee',
            BalanceService::class,
        ], $sorted);
    }

    /**
     * 价格 Hook 应按 sort_order 依次执行，并尊重 first_only 互斥。
     */
    public function test_apply_price_hook_filters_respects_sort_and_exclusive_mode(): void
    {
        Plugin::query()->updateOrCreate(
            ['code' => 'AlphaPrice'],
            ['type' => 'price', 'priority' => 0]
        );
        Plugin::query()->updateOrCreate(
            ['code' => 'BetaPrice'],
            ['type' => 'price', 'priority' => 0]
        );

        PluginCoordinationService::getInstance()->updateConfig('price', [
            'sort_order'     => ['BetaPrice', 'AlphaPrice'],
            'exclusive_mode' => 'first_only',
        ]);

        $order = [];

        listen_hook_filter('model.sku.final_price.BetaPrice', function (array $data) use (&$order) {
            $order[] = 'BetaPrice';
            $data['price'] -= 1;

            return $data;
        });

        listen_hook_filter('model.sku.final_price.AlphaPrice', function (array $data) use (&$order) {
            $order[] = 'AlphaPrice';
            $data['price'] -= 2;

            return $data;
        });

        $result = PluginCoordinationService::getInstance()->applyPriceHookFilters([
            'sku'   => null,
            'price' => 100,
        ]);

        $this->assertSame(['BetaPrice'], $order);
        $this->assertEquals(99, $result['price']);
    }

    /**
     * all_stack 模式下多个价格插件应全部执行。
     */
    public function test_apply_price_hook_filters_runs_all_plugins_in_all_stack_mode(): void
    {
        Plugin::query()->updateOrCreate(
            ['code' => 'AlphaPrice'],
            ['type' => 'price', 'priority' => 0]
        );
        Plugin::query()->updateOrCreate(
            ['code' => 'BetaPrice'],
            ['type' => 'price', 'priority' => 0]
        );

        PluginCoordinationService::getInstance()->updateConfig('price', [
            'sort_order'     => ['BetaPrice', 'AlphaPrice'],
            'exclusive_mode' => 'all_stack',
        ]);

        $order = [];

        listen_hook_filter('model.sku.final_price.BetaPrice', function (array $data) use (&$order) {
            $order[] = 'BetaPrice';
            $data['price'] -= 1;

            return $data;
        });

        listen_hook_filter('model.sku.final_price.AlphaPrice', function (array $data) use (&$order) {
            $order[] = 'AlphaPrice';
            $data['price'] -= 2;

            return $data;
        });

        $result = PluginCoordinationService::getInstance()->applyPriceHookFilters([
            'sku'   => null,
            'price' => 100,
        ]);

        $this->assertSame(['BetaPrice', 'AlphaPrice'], $order);
        $this->assertEquals(97, $result['price']);
    }
}
