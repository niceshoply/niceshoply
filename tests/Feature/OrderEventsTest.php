<?php

namespace Tests\Feature;

use App\Listeners\SendPaidOrderConfirmation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use NiceShoply\Common\Events\OrderPaid;
use NiceShoply\Common\Events\OrderStatusChanged;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Services\StateMachineService;
use Tests\TestCase;

/**
 * 核心业务流程的 Laravel Event/Listener 测试。
 *
 * 验证订单状态流转会派发领域事件，且支付成功事件已绑定异步监听。
 */
class OrderEventsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('nice.system.warehouse_enabled', false);
    }

    private function makeUnpaidOrder(): Order
    {
        $order = Order::query()->create([
            'number'                 => 'EVT-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Event Test',
            'email'                  => 'event@example.com',
            'calling_code'           => 1,
            'telephone'              => '5551234567',
            'total'                  => 30.00,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => StateMachineService::UNPAID,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => 'Test',
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '5551234567',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'CA',
            'shipping_city'          => 'LA',
            'shipping_address_1'     => '456 Test Ave',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '90001',
            'billing_method_code'    => 'stripe',
            'billing_method_name'    => 'Stripe',
            'billing_customer_name'  => 'Test',
            'billing_calling_code'   => '1',
            'billing_telephone'      => '5551234567',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'CA',
            'billing_city'           => 'LA',
            'billing_address_1'      => '456 Test Ave',
            'billing_address_2'      => '',
            'billing_zipcode'        => '90001',
        ]);

        $product = Product::query()->create([
            'active'   => true, 'position' => 0, 'sales' => 0,
            'price'    => 30, 'origin_price' => 30,
            'brand_id' => 0, 'tax_class_id' => 0, 'weight' => 0, 'weight_class' => '',
            'slug'     => 'evt-'.uniqid('', true),
            'spu_code' => 'EVT-SPU-'.uniqid('', true),
        ]);
        $sku = Sku::query()->create([
            'product_id' => $product->id,
            'code'       => 'EVT-SKU-'.uniqid(),
            'price'      => 30, 'origin_price' => 30,
            'quantity'   => 50, 'is_default' => true, 'position' => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id,
            'product_id'    => $product->id,
            'order_number'  => $order->number,
            'product_sku'   => $sku->code,
            'variant_label' => '',
            'name'          => 'Event Product',
            'image'         => '',
            'quantity'      => 1,
            'price'         => 30,
        ]);

        return $order;
    }

    public function test_status_change_dispatches_domain_events(): void
    {
        Event::fake([OrderPaid::class, OrderStatusChanged::class]);

        $order = $this->makeUnpaidOrder();
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);

        Event::assertDispatched(OrderStatusChanged::class, function ($e) use ($order) {
            return $e->order->id === $order->id
                && $e->fromStatus === StateMachineService::UNPAID
                && $e->toStatus === StateMachineService::PAID;
        });

        Event::assertDispatched(OrderPaid::class, fn ($e) => $e->order->id === $order->id);
    }

    public function test_non_paid_transition_does_not_dispatch_order_paid(): void
    {
        Event::fake([OrderPaid::class, OrderStatusChanged::class]);

        $order = $this->makeUnpaidOrder();
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::CANCELLED);

        Event::assertDispatched(OrderStatusChanged::class);
        Event::assertNotDispatched(OrderPaid::class);
    }

    public function test_order_paid_listener_is_registered(): void
    {
        $this->assertTrue(Event::hasListeners(OrderPaid::class));

        $listeners = $this->app['events']->getRawListeners()[OrderPaid::class] ?? [];
        $this->assertContains(SendPaidOrderConfirmation::class, $listeners);
    }
}
