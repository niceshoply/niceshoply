<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\Stripe\Controllers\StripeController;
use Tests\TestCase;

/**
 * Stripe Webhook 验签与支付回调测试。
 *
 * 直接调用 StripeController@callback，验证安全要求：
 *  - 未配置 webhook_secret → fail closed（400）
 *  - 签名无效 → 400
 *  - 非支付成功事件 → 200 忽略（避免 Stripe 重试）
 *  - 有效签名 + charge.succeeded → 订单流转为已支付
 *  - 订单不存在 → 200（避免 Stripe 重试）
 */
class StripeWebhookTest extends TestCase
{
    use DatabaseTransactions;

    private const SECRET = 'whsec_test_secret_key';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('nice.system.warehouse_enabled', false);
        config()->set('nice.stripe.webhook_secret', self::SECRET);
    }

    private function makeUnpaidOrder(float $total = 20.00): Order
    {
        $order = Order::query()->create([
            'number'                 => 'STRIPE-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Webhook Test',
            'email'                  => 'webhook@example.com',
            'calling_code'           => 1,
            'telephone'              => '5551234567',
            'total'                  => $total,
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
            'price'    => $total, 'origin_price' => $total,
            'brand_id' => 0, 'tax_class_id' => 0, 'weight' => 0, 'weight_class' => '',
            'slug'     => 'stripe-'.uniqid('', true),
            'spu_code' => 'STRIPE-SPU-'.uniqid('', true),
        ]);
        $sku = Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => 'STRIPE-SKU-'.uniqid(),
            'price'        => $total,
            'origin_price' => $total,
            'quantity'     => 100,
            'is_default'   => true,
            'position'     => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id,
            'product_id'    => $product->id,
            'order_number'  => $order->number,
            'product_sku'   => $sku->code,
            'variant_label' => '',
            'name'          => 'Webhook Product',
            'image'         => '',
            'quantity'      => 1,
            'price'         => $total,
        ]);

        return $order;
    }

    private function signedRequest(string $payload, ?string $secret = self::SECRET): Request
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret ?? self::SECRET);
        $header    = "t={$timestamp},v1={$signature}";

        $request = Request::create('/callback/stripe', 'POST', [], [], [], [], $payload);
        $request->headers->set('Stripe-Signature', $header);

        return $request;
    }

    private function eventPayload(string $type, string $orderNumber, int $amountMinorUnit): string
    {
        return json_encode([
            'id'   => 'evt_'.uniqid(),
            'type' => $type,
            'data' => [
                'object' => [
                    'metadata' => ['order_number' => $orderNumber],
                    'amount'   => $amountMinorUnit,
                ],
            ],
        ]);
    }

    public function test_rejects_when_webhook_secret_not_configured(): void
    {
        config()->set('nice.stripe.webhook_secret', '');

        $payload  = $this->eventPayload('charge.succeeded', 'STRIPE-NONE', 2000);
        $response = (new StripeController)->callback($this->signedRequest($payload));

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse(json_decode($response->getContent(), true)['success']);
    }

    public function test_rejects_invalid_signature(): void
    {
        $payload = $this->eventPayload('charge.succeeded', 'STRIPE-NONE', 2000);

        $request = Request::create('/callback/stripe', 'POST', [], [], [], [], $payload);
        $request->headers->set('Stripe-Signature', 't='.time().',v1=deadbeef');

        $response = (new StripeController)->callback($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse(json_decode($response->getContent(), true)['success']);
    }

    public function test_ignores_non_payment_event_types(): void
    {
        $payload  = $this->eventPayload('payment_intent.created', 'STRIPE-NONE', 2000);
        $response = (new StripeController)->callback($this->signedRequest($payload));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_unknown_order_returns_200_to_avoid_retries(): void
    {
        $payload  = $this->eventPayload('charge.succeeded', 'STRIPE-DOES-NOT-EXIST', 2000);
        $response = (new StripeController)->callback($this->signedRequest($payload));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_valid_signed_event_marks_order_paid(): void
    {
        $order = $this->makeUnpaidOrder(20.00);

        $payload  = $this->eventPayload('charge.succeeded', $order->number, 2000);
        $response = (new StripeController)->callback($this->signedRequest($payload));

        $this->assertEquals(200, $response->getStatusCode());

        $order->refresh();
        $this->assertEquals(StateMachineService::PAID, $order->status);
    }

    public function test_already_paid_order_is_idempotent(): void
    {
        $order = $this->makeUnpaidOrder(20.00);
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        $order->refresh();

        $payload  = $this->eventPayload('charge.succeeded', $order->number, 2000);
        $response = (new StripeController)->callback($this->signedRequest($payload));

        // 已支付订单重复回调应直接确认（200），不报错
        $this->assertEquals(200, $response->getStatusCode());
        $order->refresh();
        $this->assertEquals(StateMachineService::PAID, $order->status);
    }
}
