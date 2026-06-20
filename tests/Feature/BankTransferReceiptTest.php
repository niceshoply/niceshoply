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
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use Plugin\BankTransfer\Controllers\ReceiptController;
use Tests\TestCase;

/**
 * 银行转账回单上传测试。
 *
 * 重点验证安全性（防 IDOR / 未授权）与业务约束：
 *  - 仅下单本人可上传回单（归属校验）
 *  - 非银行转账订单拒绝上传
 *  - 上传仅置 certificate，paid 保持 false（由管理员人工确认）
 *  - 未登录访客拒绝（401）
 *
 * 为隔离插件路由 / JWT 启动复杂度，直接实例化控制器并以
 * actingAs(customer) 设置客户态，聚焦控制器的归属与存储逻辑。
 */
class BankTransferReceiptTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // 使用内存假磁盘，避免污染真实上传目录
        Storage::fake('upload');
    }

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'bt-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'BankTransfer Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ]);
    }

    private function makeOrder(int $customerId, string $billingMethod = 'bank_transfer'): Order
    {
        return Order::query()->create([
            'number'                 => 'BT-'.uniqid(),
            'customer_id'            => $customerId,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'BankTransfer Tester',
            'email'                  => 'bt-buyer@example.com',
            'calling_code'           => 1,
            'telephone'              => '5551234567',
            'total'                  => 99.00,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => 'unpaid',
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
            'billing_method_code'    => $billingMethod,
            'billing_method_name'    => ucfirst($billingMethod),
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
    }

    private function uploadRequest(string $number): Request
    {
        $file    = UploadedFile::fake()->image('receipt.jpg', 200, 200);
        $request = Request::create("/api/v1/orders/{$number}/receipt", 'POST', [], [], ['receipt' => $file]);
        $request->headers->set('Accept', 'application/json');

        return $request;
    }

    private function decode(mixed $response): array
    {
        return json_decode($response->getContent(), true) ?: [];
    }

    public function test_owner_can_upload_receipt_and_payment_stays_unpaid(): void
    {
        $customer = $this->makeCustomer();
        $order    = $this->makeOrder($customer->id);

        $this->actingAs($customer, 'customer');

        $response = (new ReceiptController)->upload($this->uploadRequest($order->number), $order->number);

        $this->assertEquals(200, $response->getStatusCode());
        $body = $this->decode($response);
        $this->assertTrue($body['success']);

        // 回单仅写 certificate，paid 必须保持 false（人工确认前不可置为已付）
        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertFalse((bool) $payment->paid);
        $this->assertNotEmpty($payment->certificate);
    }

    public function test_non_owner_cannot_upload_receipt(): void
    {
        $owner = $this->makeCustomer();
        $other = $this->makeCustomer();
        $order = $this->makeOrder($owner->id);

        // 以「非下单人」身份尝试上传他人订单回单
        $this->actingAs($other, 'customer');

        $response = (new ReceiptController)->upload($this->uploadRequest($order->number), $order->number);

        // 归属校验：查询被 customer_id 限定，订单对非本人不可见 → 404
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($this->decode($response)['success']);

        // 不应写入任何 payment 记录
        $this->assertNull($order->payments()->first());
    }

    public function test_guest_cannot_upload_receipt(): void
    {
        $owner = $this->makeCustomer();
        $order = $this->makeOrder($owner->id);

        // 不登录任何客户（访客）
        $response = (new ReceiptController)->upload($this->uploadRequest($order->number), $order->number);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertFalse($this->decode($response)['success']);
    }

    public function test_non_bank_transfer_order_rejected(): void
    {
        $customer = $this->makeCustomer();
        $order    = $this->makeOrder($customer->id, 'stripe');

        $this->actingAs($customer, 'customer');

        $response = (new ReceiptController)->upload($this->uploadRequest($order->number), $order->number);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($this->decode($response)['success']);
    }
}
