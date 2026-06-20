<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Visit\ConversionDaily;
use NiceShoply\Common\Models\Visit\VisitDaily;
use NiceShoply\Common\Models\Visit\VisitEvent;
use NiceShoply\Common\Services\VisitStatisticsService;
use Tests\TestCase;

/**
 * 访问追踪与转化分析测试（IMP-05 / IMP-18）
 *
 * 覆盖：
 * - 事件入库
 * - 每日访问聚合（PV/UV/IP）
 * - 转化漏斗聚合与转化率计算
 */
class VisitTrackingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 构造一批事件并验证聚合结果。
     */
    public function test_aggregation_computes_pv_and_conversion_rates(): void
    {
        $date = Carbon::create(2026, 6, 10, 10, 0, 0);

        // 会话 A：页面浏览 + 商品浏览 + 加购 + 结账 + 下单 + 支付
        $this->makeEvent('sess-A', VisitEvent::TYPE_PAGE_VIEW, '1.1.1.1', $date);
        $this->makeEvent('sess-A', VisitEvent::TYPE_PRODUCT_VIEW, '1.1.1.1', $date);
        $this->makeEvent('sess-A', VisitEvent::TYPE_ADD_TO_CART, '1.1.1.1', $date);
        $this->makeEvent('sess-A', VisitEvent::TYPE_CHECKOUT_START, '1.1.1.1', $date);
        $this->makeEvent('sess-A', VisitEvent::TYPE_ORDER_PLACED, '1.1.1.1', $date);
        $this->makeEvent('sess-A', VisitEvent::TYPE_PAYMENT_COMPLETED, '1.1.1.1', $date);

        // 会话 B：仅页面浏览 + 加购（未结账）
        $this->makeEvent('sess-B', VisitEvent::TYPE_PAGE_VIEW, '2.2.2.2', $date);
        $this->makeEvent('sess-B', VisitEvent::TYPE_ADD_TO_CART, '2.2.2.2', $date);

        $service = new VisitStatisticsService;
        $service->aggregateDaily($date->copy());

        // 访问统计：PV = 2 个 page_view，UV = 2 会话，IP = 2
        $visitDaily = VisitDaily::where('date', $date->toDateString())->first();
        $this->assertNotNull($visitDaily);
        $this->assertSame(2, (int) $visitDaily->pv);
        $this->assertSame(2, (int) $visitDaily->uv);
        $this->assertSame(2, (int) $visitDaily->ip);

        // 转化统计：加购 2、结账 1、下单 1、支付 1
        $conversion = ConversionDaily::where('date', $date->toDateString())->first();
        $this->assertNotNull($conversion);
        $this->assertSame(2, (int) $conversion->add_to_carts);
        $this->assertSame(1, (int) $conversion->checkout_starts);
        $this->assertSame(1, (int) $conversion->order_placed);
        $this->assertSame(1, (int) $conversion->payment_completed);

        // 加购 → 结账 转化率 = 1/2 = 50% → 存储为 5000（x100）
        $this->assertSame(5000, (int) $conversion->cart_to_checkout_rate);
        // 百分比 accessor
        $this->assertEquals(50.0, $conversion->cart_to_checkout_percent);
    }

    /**
     * 创建一条访问事件。
     */
    private function makeEvent(string $sessionId, string $type, string $ip, Carbon $at): void
    {
        VisitEvent::create([
            'session_id' => $sessionId,
            'event_type' => $type,
            'event_data' => [],
            'ip_address' => $ip,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
