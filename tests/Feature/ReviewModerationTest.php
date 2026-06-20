<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Services\Review\ReviewService;
use NiceShoply\Common\Services\StateMachineService;
use Tests\TestCase;

/**
 * 评价审核与已购校验集成测试。
 */
class ReviewModerationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nice.system.bought_review'          => true,
            'nice.system.review_audit'           => true,
            'nice.system.review_sensitive_words' => "spam\nbadword",
        ]);
    }

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'review-'.uniqid().'@example.com',
            'password'          => bcrypt('secret'),
            'name'              => 'Reviewer',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();
    }

    /**
     * @return array{customer: Customer, product: Product, orderItem: Item}
     */
    private function makePaidOrderItem(): array
    {
        $customer = $this->makeCustomer();
        $product  = Product::query()->create([
            'active'   => true, 'position' => 0, 'sales' => 0,
            'price'    => 99, 'origin_price' => 99,
            'brand_id' => 0, 'tax_class_id' => 0,
            'weight'   => 0, 'weight_class' => '',
            'slug'     => 'rev-'.uniqid('', true),
            'spu_code' => 'REV-SPU-'.uniqid('', true),
        ]);
        $sku = Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => 'REV-SKU-'.uniqid(),
            'price'        => 99,
            'origin_price' => 99,
            'quantity'     => 10,
            'is_default'   => true,
            'position'     => 0,
        ]);
        $order = Order::query()->create([
            'number'                 => 'REV-'.uniqid(),
            'customer_id'            => $customer->id,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => $customer->name,
            'email'                  => $customer->email,
            'calling_code'           => 86,
            'telephone'              => '13800000000',
            'total'                  => 99,
            'locale'                 => 'zh-cn',
            'currency_code'          => 'CNY',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => StateMachineService::PAID,
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat',
            'shipping_customer_name' => 'T',
            'shipping_calling_code'  => '86',
            'shipping_telephone'     => '13800000000',
            'shipping_country'       => 'CN',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'GD',
            'shipping_city'          => 'SZ',
            'shipping_address_1'     => 'Test',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '518000',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'COD',
            'billing_customer_name'  => 'T',
            'billing_calling_code'   => '86',
            'billing_telephone'      => '13800000000',
            'billing_country'        => 'CN',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'GD',
            'billing_city'           => 'SZ',
            'billing_address_1'      => 'Test',
            'billing_address_2'      => '',
            'billing_zipcode'        => '518000',
        ]);
        $orderItem = Item::query()->create([
            'order_id'      => $order->id,
            'product_id'    => $product->id,
            'order_number'  => $order->number,
            'product_sku'   => $sku->code,
            'variant_label' => '',
            'name'          => 'Test Product',
            'image'         => '',
            'quantity'      => 1,
            'price'         => 99,
        ]);

        return compact('customer', 'product', 'orderItem');
    }

    public function test_purchased_review_creates_pending_with_sanitized_content(): void
    {
        $ctx = $this->makePaidOrderItem();

        $review = ReviewRepo::getInstance()->create([
            'customer_id'   => $ctx['customer']->id,
            'order_item_id' => $ctx['orderItem']->id,
            'product_id'    => $ctx['product']->id,
            'rating'        => 5,
            'content'       => 'Great product spam<script>alert(1)</script>',
            'images'        => "https://example.com/a.jpg\nhttps://example.com/b.jpg",
        ]);

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals(Review::STATUS_PENDING, $review->status);
        $this->assertFalse($review->active);
        $this->assertStringNotContainsString('script', $review->content);
        $this->assertStringContainsString('****', $review->content);
        $this->assertCount(2, $review->images);
    }

    public function test_non_purchaser_cannot_review(): void
    {
        $ctx      = $this->makePaidOrderItem();
        $intruder = $this->makeCustomer();

        $this->expectException(\Exception::class);

        ReviewRepo::getInstance()->create([
            'customer_id'   => $intruder->id,
            'order_item_id' => $ctx['orderItem']->id,
            'product_id'    => $ctx['product']->id,
            'rating'        => 5,
            'content'       => 'Fake review',
        ]);
    }

    public function test_approve_makes_review_public_and_stats(): void
    {
        $ctx = $this->makePaidOrderItem();

        $review = ReviewRepo::getInstance()->create([
            'customer_id'   => $ctx['customer']->id,
            'order_item_id' => $ctx['orderItem']->id,
            'product_id'    => $ctx['product']->id,
            'rating'        => 5,
            'content'       => 'Excellent',
        ]);

        ReviewService::getInstance()->approve($review);

        $stats = ReviewService::getInstance()->getProductStats($ctx['product']->id);
        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(100.0, $stats['rate']);

        $list = ReviewRepo::getInstance()->getListByProduct($ctx['product']->id, 10, 1, ['has_images' => false]);
        $this->assertEquals(1, $list->total());
    }

    public function test_filter_has_images_on_front_list(): void
    {
        $ctx = $this->makePaidOrderItem();

        $withImage = Review::query()->create([
            'customer_id'   => $ctx['customer']->id,
            'product_id'    => $ctx['product']->id,
            'order_item_id' => $ctx['orderItem']->id,
            'rating'        => 4,
            'content'       => 'With photo',
            'images'        => ['https://example.com/1.jpg'],
            'like'          => 0,
            'dislike'       => 0,
            'status'        => Review::STATUS_APPROVED,
            'active'        => true,
        ]);

        Review::query()->create([
            'customer_id'   => $ctx['customer']->id,
            'product_id'    => $ctx['product']->id,
            'order_item_id' => 0,
            'rating'        => 3,
            'content'       => 'Text only',
            'like'          => 0,
            'dislike'       => 0,
            'status'        => Review::STATUS_APPROVED,
            'active'        => true,
        ]);

        $filtered = ReviewRepo::getInstance()->getListByProduct($ctx['product']->id, 10, 1, ['has_images' => true]);

        $this->assertEquals(1, $filtered->total());
        $this->assertEquals($withImage->id, $filtered->first()->id);
    }
}
