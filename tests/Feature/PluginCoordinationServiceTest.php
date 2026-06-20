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
use NiceShoply\Common\Models\PluginCoordination;
use NiceShoply\Common\Services\PluginCoordinationService;
use Tests\TestCase;

/**
 * 插件协调服务测试
 *
 * 覆盖：配置增改、互斥冲突判定、跳过逻辑。
 */
class PluginCoordinationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PluginCoordinationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = PluginCoordinationService::getInstance();
    }

    /**
     * 更新配置应持久化排序与互斥模式。
     */
    public function test_update_config_persists_settings(): void
    {
        $config = $this->service->updateConfig('orderfee', [
            'sort_order'     => ['CouponDiscount', 'OrderDiscount'],
            'exclusive_mode' => 'first_only',
        ]);

        $this->assertInstanceOf(PluginCoordination::class, $config);
        $this->assertSame(['CouponDiscount', 'OrderDiscount'], $config->getSortOrder());
        $this->assertTrue($config->isFirstOnlyMode());
    }

    /**
     * all_stack 模式下永不跳过。
     */
    public function test_should_not_skip_in_all_stack_mode(): void
    {
        $this->service->updateConfig('orderfee', [
            'sort_order'     => [],
            'exclusive_mode' => 'all_stack',
        ]);

        $this->assertFalse(
            $this->service->shouldSkip('orderfee', 'OrderDiscount', ['CouponDiscount'])
        );
    }

    /**
     * first_only 模式下已有插件应用后应跳过其余。
     */
    public function test_should_skip_in_first_only_mode(): void
    {
        $this->service->updateConfig('orderfee', [
            'sort_order'     => [],
            'exclusive_mode' => 'first_only',
        ]);

        $this->assertTrue(
            $this->service->shouldSkip('orderfee', 'OrderDiscount', ['CouponDiscount'])
        );
        // 无已应用插件时不跳过
        $this->assertFalse(
            $this->service->shouldSkip('orderfee', 'OrderDiscount', [])
        );
    }

    /**
     * custom 模式下命中互斥对才跳过。
     */
    public function test_custom_mode_exclusive_conflict(): void
    {
        $this->service->updateConfig('price', [
            'sort_order'      => [],
            'exclusive_mode'  => 'custom',
            'exclusive_pairs' => [['MemberPrice', 'SalePrice']],
        ]);

        // 命中互斥对：跳过
        $this->assertTrue(
            $this->service->shouldSkip('price', 'SalePrice', ['MemberPrice'])
        );
        // 未命中互斥对：不跳过
        $this->assertFalse(
            $this->service->shouldSkip('price', 'BulkPrice', ['MemberPrice'])
        );
    }
}
