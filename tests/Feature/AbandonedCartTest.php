<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use NiceShoply\Common\Models\AbandonedCart;
use NiceShoply\Common\Models\CartItem;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Notifications\AbandonedCartNotification;
use NiceShoply\Common\Services\AbandonedCart\AbandonedCartService;
use Tests\TestCase;

/**
 * 弃购扫描、召回通知与转化标记集成测试。
 */
class AbandonedCartTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nice.system.abandoned_cart_enabled'                 => true,
            'nice.system.abandoned_cart_idle_hours'              => 24,
            'nice.system.abandoned_cart_max_reminders'           => 3,
            'nice.system.abandoned_cart_reminder_interval_hours' => 24,
            'nice.system.abandoned_cart_coupon_enabled'          => true,
            'nice.system.abandoned_cart_coupon_type'             => 'percent',
            'nice.system.abandoned_cart_coupon_value'            => 10,
            'nice.system.abandoned_cart_coupon_min_amount'       => 0,
            'nice.system.email_engine'                           => 'log',
            'nice.system.email_notifications'                    => ['abandoned_cart'],
        ]);
    }

    /**
     * @return array{customer: Customer, product: Product, sku: Sku, cartItem: CartItem}
     */
    private function makeStaleCart(): array
    {
        $customer = Customer::query()->create([
            'email'             => 'abandon-'.uniqid().'@example.com',
            'password'          => bcrypt('secret'),
            'name'              => 'Abandon Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();

        $product = Product::query()->create([
            'active'       => true,
            'position'     => 0,
            'sales'        => 0,
            'price'        => 50,
            'origin_price' => 50,
            'brand_id'     => 0,
            'tax_class_id' => 0,
            'weight'       => 1,
            'weight_class' => '',
            'slug'         => 'ab-'.uniqid('', true),
            'spu_code'     => 'AB-SPU-'.uniqid('', true),
        ]);

        $sku = Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => 'AB-SKU-'.uniqid(),
            'price'        => 50,
            'origin_price' => 50,
            'quantity'     => 100,
            'is_default'   => true,
            'position'     => 0,
        ]);

        $cartItem = CartItem::query()->create([
            'customer_id' => $customer->id,
            'guest_id'    => '',
            'product_id'  => $product->id,
            'sku_code'    => $sku->code,
            'quantity'    => 2,
            'selected'    => true,
            'item_type'   => 'normal',
        ]);

        // 模拟超过闲置阈值的购物车
        CartItem::query()->where('id', $cartItem->id)->update([
            'updated_at' => Carbon::now()->subHours(30),
        ]);
        $cartItem->refresh();

        return compact('customer', 'product', 'sku', 'cartItem');
    }

    public function test_scan_sends_reminder_and_attaches_coupon(): void
    {
        Notification::fake();

        ['customer' => $customer] = $this->makeStaleCart();

        $result = AbandonedCartService::getInstance()->scanAndRemind();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(1, $result['reminded']);

        $record = AbandonedCart::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($record);
        $this->assertSame(1, $record->reminder_count);
        $this->assertNotEmpty($record->coupon_code);
        $this->assertTrue(Coupon::query()->where('code', $record->coupon_code)->exists());

        Notification::assertSentTo($customer, AbandonedCartNotification::class);
    }

    public function test_mark_converted_after_checkout(): void
    {
        Notification::fake();

        ['customer' => $customer] = $this->makeStaleCart();
        AbandonedCartService::getInstance()->scanAndRemind();

        $order = new Order([
            'customer_id' => $customer->id,
        ]);
        $order->id = 90001;

        AbandonedCartService::getInstance()->markConverted($order);

        $record = AbandonedCart::query()->where('customer_id', $customer->id)->first();
        $this->assertTrue($record->converted);
        $this->assertSame(90001, $record->converted_order_id);
        $this->assertNotNull($record->converted_at);
    }

    public function test_guest_cart_is_skipped(): void
    {
        Notification::fake();

        $product = Product::query()->create([
            'active'       => true,
            'position'     => 0,
            'sales'        => 0,
            'price'        => 30,
            'origin_price' => 30,
            'brand_id'     => 0,
            'tax_class_id' => 0,
            'weight'       => 1,
            'weight_class' => '',
            'slug'         => 'guest-'.uniqid('', true),
            'spu_code'     => 'G-SPU-'.uniqid('', true),
        ]);

        $sku = Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => 'G-SKU-'.uniqid(),
            'price'        => 30,
            'origin_price' => 30,
            'quantity'     => 50,
            'is_default'   => true,
            'position'     => 0,
        ]);

        $item = CartItem::query()->create([
            'customer_id' => 0,
            'guest_id'    => 'guest-'.uniqid(),
            'product_id'  => $product->id,
            'sku_code'    => $sku->code,
            'quantity'    => 1,
            'selected'    => true,
            'item_type'   => 'normal',
        ]);
        CartItem::query()->where('id', $item->id)->update([
            'updated_at' => Carbon::now()->subHours(30),
        ]);

        $result = AbandonedCartService::getInstance()->scanAndRemind();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(0, $result['reminded']);
        Notification::assertNothingSent();
    }

    public function test_stats_reflect_conversion_rate(): void
    {
        AbandonedCart::query()->create([
            'cart_key'       => 'c:1',
            'customer_id'    => 1,
            'email'          => 'a@example.com',
            'cart_snapshot'  => [],
            'cart_total'     => 100,
            'currency_code'  => 'CNY',
            'reminder_count' => 1,
            'converted'      => true,
        ]);
        AbandonedCart::query()->create([
            'cart_key'       => 'c:2',
            'customer_id'    => 2,
            'email'          => 'b@example.com',
            'cart_snapshot'  => [],
            'cart_total'     => 50,
            'currency_code'  => 'CNY',
            'reminder_count' => 1,
            'converted'      => false,
        ]);

        $stats = AbandonedCartService::getInstance()->getStats();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['converted']);
        $this->assertSame(50.0, $stats['rate']);
    }
}
