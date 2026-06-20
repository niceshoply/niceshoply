<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\ShippingZone;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台配送区域 Controller 测试。
 */
class ShippingZoneControllerTest extends ConsoleTestCase
{
    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['shipping_zones_index']);
        $this->get($this->consoleUrl('shipping_zones.index'))->assertStatus(200);
    }

    public function test_store_creates_zone_with_parsed_ids(): void
    {
        $this->loginAdmin(['shipping_zones_store']);

        $name = 'Zone-'.uniqid();
        $this->post($this->consoleUrl('shipping_zones.store'), [
            'name'        => $name,
            'country_ids' => '1, 2, 3',
            'state_ids'   => '',
            'priority'    => 5,
            'active'      => 1,
        ])->assertRedirect($this->consoleUrl('shipping_zones.index'));

        $zone = ShippingZone::query()->where('name', $name)->first();
        $this->assertNotNull($zone);
        $this->assertEquals([1, 2, 3], $zone->country_ids);
    }

    public function test_destroy_removes_zone(): void
    {
        $this->loginAdmin(['shipping_zones_destroy']);

        $zone = ShippingZone::query()->create([
            'name' => 'Del-'.uniqid(), 'country_ids' => [], 'state_ids' => [], 'priority' => 0, 'active' => true,
        ]);

        $this->delete($this->consoleUrl('shipping_zones.destroy', $zone->id))->assertRedirect();
        $this->assertDatabaseMissing('nice_shipping_zones', ['id' => $zone->id]);
    }
}
