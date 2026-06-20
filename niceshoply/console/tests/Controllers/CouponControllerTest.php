<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Coupon;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台优惠券 Controller 测试（含批量生成）。
 */
class CouponControllerTest extends ConsoleTestCase
{
    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['coupons_index']);
        $this->get($this->consoleUrl('coupons.index'))->assertStatus(200);
    }

    public function test_store_creates_single_coupon(): void
    {
        $this->loginAdmin(['coupons_store']);

        $code = 'SAVE'.strtoupper(uniqid());
        $this->post($this->consoleUrl('coupons.store'), [
            'code'        => $code, 'type' => 'fixed', 'value' => 20, 'min_amount' => 100,
            'total_limit' => 0, 'per_customer_limit' => 1, 'active' => 1,
        ])->assertRedirect($this->consoleUrl('coupons.index'));

        $this->assertDatabaseHas('nice_coupons', ['code' => $code, 'type' => 'fixed']);
    }

    public function test_store_batch_generates_multiple_coupons(): void
    {
        $this->loginAdmin(['coupons_store']);

        $before = Coupon::query()->count();

        $this->post($this->consoleUrl('coupons.store'), [
            'type'        => 'percent', 'value' => 10, 'active' => 1,
            'batch_count' => 5, 'batch_prefix' => 'B',
        ])->assertRedirect($this->consoleUrl('coupons.index'));

        $this->assertEquals($before + 5, Coupon::query()->count());
    }

    public function test_destroy_removes_coupon(): void
    {
        $this->loginAdmin(['coupons_destroy']);

        $coupon = Coupon::query()->create([
            'code' => 'DEL'.strtoupper(uniqid()), 'type' => 'fixed', 'value' => 5, 'active' => true,
        ]);

        $this->delete($this->consoleUrl('coupons.destroy', $coupon->id))->assertRedirect();
        $this->assertDatabaseMissing('nice_coupons', ['id' => $coupon->id]);
    }

    public function test_usages_page_accessible(): void
    {
        $this->loginAdmin(['coupons_usages']);

        $coupon = Coupon::query()->create([
            'code' => 'USE'.strtoupper(uniqid()), 'type' => 'fixed', 'value' => 5, 'active' => true,
        ]);

        $this->get($this->consoleUrl('coupons.usages', $coupon->id))->assertStatus(200);
    }
}
