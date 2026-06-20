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
use NiceShoply\Common\Models\ShippingTemplate;
use NiceShoply\Common\Models\ShippingZone;
use NiceShoply\Common\Services\Shipping\ShippingTemplateService;
use Tests\TestCase;

/**
 * 内置运费计算测试。
 *
 * 覆盖固定运费、按重量/件数/金额的阶梯与单位费率、满额包邮，
 * 以及配送区域目的地匹配逻辑。
 */
class ShippingTemplateServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTemplate(array $attributes): ShippingTemplate
    {
        return ShippingTemplate::query()->create(array_merge([
            'name'           => 'T-'.uniqid(),
            'zone_id'        => null,
            'calc_type'      => 'flat',
            'rules'          => [],
            'free_threshold' => 0,
            'priority'       => 0,
            'active'         => true,
        ], $attributes));
    }

    public function test_flat_rate_uses_base(): void
    {
        $template = $this->makeTemplate([
            'calc_type' => 'flat',
            'rules'     => ['base' => 8],
        ]);

        $cost = ShippingTemplateService::getInstance()->calculateCost($template, 100, 2, 1);

        $this->assertEquals(8, $cost);
    }

    public function test_free_threshold_overrides_cost(): void
    {
        $template = $this->makeTemplate([
            'calc_type'      => 'flat',
            'rules'          => ['base' => 8],
            'free_threshold' => 100,
        ]);

        $this->assertEquals(0, ShippingTemplateService::getInstance()->calculateCost($template, 150, 2, 1));
        $this->assertEquals(8, ShippingTemplateService::getInstance()->calculateCost($template, 80, 2, 1));
    }

    public function test_by_weight_rate_and_unit(): void
    {
        $template = $this->makeTemplate([
            'calc_type' => 'by_weight',
            'rules'     => ['base' => 5, 'rate' => 2, 'unit' => 1],
        ]);

        // base 5 + rate 2 * ceil(3.2/1)=4 => 5 + 8 = 13
        $cost = ShippingTemplateService::getInstance()->calculateCost($template, 100, 3.2, 1);

        $this->assertEquals(13, $cost);
    }

    public function test_by_qty_tiers(): void
    {
        $template = $this->makeTemplate([
            'calc_type' => 'by_qty',
            'rules'     => ['tiers' => [['max' => 1, 'cost' => 5], ['max' => 5, 'cost' => 12]]],
        ]);

        $this->assertEquals(5, ShippingTemplateService::getInstance()->calculateCost($template, 100, 1, 1));
        $this->assertEquals(12, ShippingTemplateService::getInstance()->calculateCost($template, 100, 1, 4));
        // 超过最高档用最后一档
        $this->assertEquals(12, ShippingTemplateService::getInstance()->calculateCost($template, 100, 1, 9));
    }

    public function test_zone_matches_country(): void
    {
        $zone = ShippingZone::query()->create([
            'name'        => 'Z-'.uniqid(),
            'country_ids' => [1, 2],
            'state_ids'   => [],
            'priority'    => 1,
            'active'      => true,
        ]);

        $this->assertTrue($zone->matches(1, 0));
        $this->assertFalse($zone->matches(9, 0));
    }

    public function test_zone_empty_country_matches_all(): void
    {
        $zone = ShippingZone::query()->create([
            'name'        => 'Z-'.uniqid(),
            'country_ids' => [],
            'state_ids'   => [],
            'priority'    => 0,
            'active'      => true,
        ]);

        $this->assertTrue($zone->matches(123, 456));
    }
}
