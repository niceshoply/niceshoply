<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Shipment;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Repositories\Customer\TransactionRepo;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Services\Warehouse\AllocationService;
use Throwable;

class StateMachineService
{
    private Order $order;

    private int $orderId;

    private string $comment;

    private bool $notify;

    private array $shipment = [];

    private array $payment = [];

    /**
     * Created, initial status.
     */
    public const CREATED = 'created';

    /**
     * Unpaid
     */
    public const UNPAID = 'unpaid';

    /**
     * Paid
     */
    public const PAID = 'paid';

    /**
     * Shipped
     */
    public const SHIPPED = 'shipped';

    /**
     * Partially Shipped (multi-warehouse)
     */
    public const PARTIALLY_SHIPPED = 'partially_shipped';

    /**
     * Completed
     */
    public const COMPLETED = 'completed';

    /**
     * Cancelled
     */
    public const CANCELLED = 'cancelled';

    public const ORDER_STATUS = [
        self::CREATED,
        self::UNPAID,
        self::PAID,
        self::PARTIALLY_SHIPPED,
        self::SHIPPED,
        self::COMPLETED,
        self::CANCELLED,
    ];

    /**
     * Order process by state machine
     */
    public const MACHINES = [
        self::CREATED => [
            self::UNPAID => ['updateStatus', 'addHistory', 'redeemBalance', 'redeemPoints', 'notifyNewOrder'],
        ],
        self::UNPAID => [
            self::PAID      => ['updateStatus', 'addHistory', 'updateSales', 'subStock', 'notifyUpdateOrder'],
            self::CANCELLED => ['updateStatus', 'addHistory', 'revokeBalance', 'revokePromotion', 'revokePoints', 'releaseWarehouseStock', 'notifyUpdateOrder'],
        ],
        self::PAID => [
            self::CANCELLED         => ['updateStatus', 'addHistory', 'revokeBalance', 'revokePromotion', 'revokePoints', 'releaseWarehouseStock', 'notifyUpdateOrder'],
            self::PARTIALLY_SHIPPED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
            self::SHIPPED           => ['updateStatus', 'addHistory', 'addShipment', 'notifyUpdateOrder'],
            self::COMPLETED         => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
        self::PARTIALLY_SHIPPED => [
            self::SHIPPED   => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
            self::COMPLETED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
        self::SHIPPED => [
            self::COMPLETED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
    ];

    /**
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order   = $order;
        $this->orderId = $order->id;
    }

    /**
     * @param  $order
     * @return self
     */
    public static function getInstance($order): self
    {
        return new self($order);
    }

    /**
     * Set order comment.
     *
     * @param  $comment
     * @return $this
     */
    public function setComment($comment): self
    {
        $this->comment = (string) $comment;

        return $this;
    }

    /**
     * Set order notify or not.
     *
     * @param  $flag
     * @return $this
     */
    public function setNotify($flag): self
    {
        $this->notify = (bool) $flag;

        return $this;
    }

    /**
     * Set order shipment.
     *
     * @param  array  $shipment
     * @return $this
     */
    public function setShipment(array $shipment = []): self
    {
        $this->shipment = $shipment;

        return $this;
    }

    /**
     * Set order payment.
     *
     * @param  array  $payment
     * @return $this
     */
    public function setPayment(array $payment = []): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get all order statuses.
     *
     * @return array
     * @throws Exception
     */
    public static function getAllStatuses(): array
    {
        $result   = [];
        $statuses = self::ORDER_STATUS;
        foreach ($statuses as $status) {
            $result[] = [
                'status' => $status,
                'name'   => console_trans("order.$status"),
            ];
        }

        return fire_hook_filter('service.state_machine.all_statuses', $result);
    }

    /**
     * Get all valid statuses, from paid to complete.
     *
     * @return string[]
     */
    public static function getValidStatuses(): array
    {
        return [
            self::PAID,
            self::PARTIALLY_SHIPPED,
            self::SHIPPED,
            self::COMPLETED,
        ];
    }

    /**
     * Retrieve the possible states that the current order can transition to.
     *
     * @return array
     * @throws Exception
     */
    public function nextBackendStatuses(): array
    {
        $machines = $this->getMachines();

        $currentStatusCode = $this->order->status;
        $nextStatus        = $machines[$currentStatusCode] ?? [];

        if (empty($nextStatus)) {
            return [];
        }
        $nextStatusCodes = array_keys($nextStatus);
        $result          = [];
        foreach ($nextStatusCodes as $status) {
            $result[] = [
                'status' => $status,
                'name'   => trans("console/order.{$status}"),
            ];
        }

        return $result;
    }

    /**
     * External method invocation to modify the order status and process others.
     *
     * @param  $status
     * @param  string|null  $comment
     * @param  bool  $notify
     * @throws Exception
     */
    public function changeStatus($status, ?string $comment = '', bool $notify = false): void
    {
        $order         = $this->order;
        $oldStatusCode = $order->status;
        $newStatusCode = $status;

        $this->setComment($comment)->setNotify($notify);
        $this->validStatusCode($status);

        // Sentry 性能追踪
        $parentSpan = \Sentry\SentrySdk::getCurrentHub()->getSpan();
        $span       = null;
        if ($parentSpan !== null) {
            $spanContext = new \Sentry\Tracing\SpanContext;
            $spanContext->setOp('order.state_transition');
            $spanContext->setDescription("Order #{$order->number}: {$oldStatusCode} -> {$status}");
            $span = $parentSpan->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span);
        }

        DB::beginTransaction();
        try {
            $functions = $this->getFunctions($oldStatusCode, $newStatusCode);
            if ($functions) {
                foreach ($functions as $function) {
                    if ($function instanceof \Closure) {
                        $function();

                        continue;
                    }

                    if (! method_exists($this, $function)) {
                        throw new Exception("{$function} not exist in StateMachine!");
                    }
                    $this->{$function}($oldStatusCode, $status);
                }
            }
            $data = ['order' => $order, 'status' => $status, 'comment' => $comment, 'notify' => $notify];
            fire_hook_action('service.state_machine.change_status.after', $data);

            Log::channel('order')->info('order.status_changed', [
                'order_id'     => $this->orderId,
                'order_number' => $order->number,
                'old_status'   => $oldStatusCode,
                'new_status'   => $status,
                'comment'      => $comment,
                'notify'       => $notify,
                'operator'     => auth('admin')->user()?->name ?? 'system',
            ]);

            if (! $order->shipping_method_code && $status == self::PAID) {
                $this->changeStatus(self::COMPLETED, $comment, $notify);
            }
            DB::commit();

            // 提交后派发领域事件（与 Hook 互补，便于队列化异步处理）
            event(new \NiceShoply\Common\Events\OrderStatusChanged($order, (string) $oldStatusCode, (string) $newStatusCode));
            if ($newStatusCode === self::PAID) {
                event(new \NiceShoply\Common\Events\OrderPaid($order));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            if ($span !== null) {
                $span->finish();
                \Sentry\SentrySdk::getCurrentHub()->setSpan($parentSpan);
            }
        }
    }

    /**
     * Check if the current order can be transitioned to a specific status.
     *
     * @param  $statusCode
     * @throws Exception
     */
    private function validStatusCode($statusCode): void
    {
        $orderId           = $this->orderId;
        $orderNumber       = $this->order->number;
        $currentStatusCode = $this->order->status;

        $nextStatusCodes = collect($this->nextBackendStatuses())->pluck('status')->toArray();
        if (! in_array($statusCode, $nextStatusCodes)) {
            throw new Exception("Order {$orderId}({$orderNumber}) is {$currentStatusCode}, cannot be changed to $statusCode");
        }
    }

    /**
     * Retrieve the state machine process, which can be modified by external plugins through a filter hook.
     *
     * @return mixed
     */
    private function getMachines(): mixed
    {
        $data = [
            'order'    => $this->order,
            'machines' => self::MACHINES,
        ];

        $data = fire_hook_filter('service.state_machine.machines', $data);

        return $data['machines'] ?? [];
    }

    /**
     * Retrieve the events that need to be triggered based on the current order status,
     * and the status it is about to transition to.
     *
     * @param  $oldStatus
     * @param  $newStatus
     * @return array
     */
    private function getFunctions($oldStatus, $newStatus): array
    {
        $machines = $this->getMachines();

        return $machines[$oldStatus][$newStatus] ?? [];
    }

    /**
     * Update order status.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function updateStatus($oldCode, $newCode): void
    {
        $this->order->status = $newCode;
        $this->order->saveOrFail();
    }

    /**
     * Update the sales volume of the order products.
     *
     * @return void
     */
    private function updateSales(): void
    {
        $this->order->loadMissing([
            'items',
        ]);
        $orderItems = $this->order->items;
        foreach ($orderItems as $orderItem) {
            Product::query()->where('id', $orderItem->product_id)
                ->increment('sales', $orderItem->quantity);
        }
    }

    /**
     * Add an order modification record.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addHistory($oldCode, $newCode): void
    {
        $history = new Order\History([
            'order_id' => $this->orderId,
            'status'   => $newCode,
            'notify'   => (int) $this->notify,
            'comment'  => (string) $this->comment,
        ]);
        $history->saveOrFail();
    }

    /**
     * Deduct the inventory of the corresponding products for the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function subStock($oldCode, $newCode): void
    {
        // In warehouse mode, stock is already reserved during checkout — skip direct deduction
        if (system_setting('warehouse_enabled', false)) {
            return;
        }

        $this->order->loadMissing([
            'items.productSku',
        ]);
        $orderItems = $this->order->items;
        foreach ($orderItems as $orderItem) {
            $productSku = $orderItem->productSku;
            if (empty($productSku)) {
                continue;
            }
            $productSku->decrement('quantity', $orderItem->quantity);
        }
    }

    /**
     * Release warehouse reserved stock on order cancellation.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function releaseWarehouseStock($oldCode, $newCode): void
    {
        if (! system_setting('warehouse_enabled', false)) {
            return;
        }

        AllocationService::getInstance()->release($this->order);
    }

    /**
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function redeemBalance($oldCode, $newCode): void
    {
        $this->handleBalance('redeem');
    }

    /**
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function revokeBalance($oldCode, $newCode): void
    {
        $this->handleBalance('revoke');
    }

    /**
     * 订单取消时回滚促销/优惠券用量并清理流水。
     *
     * 释放促销与优惠券的 used_count（下限 0），删除核销与流水记录，
     * 使该笔订单占用的额度可被后续订单复用。
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function revokePromotion($oldCode, $newCode): void
    {
        \NiceShoply\Common\Services\Promotion\CouponService::getInstance()->rollback($this->order);
        \NiceShoply\Common\Services\Promotion\PromotionService::getInstance()->rollback($this->order);
    }

    /**
     * 订单进入 unpaid 时扣减结账使用的积分。
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function redeemPoints($oldCode, $newCode): void
    {
        try {
            \NiceShoply\Common\Services\Member\PointService::getInstance()->redeemForOrder($this->order);
        } catch (Throwable $e) {
            Log::error('积分扣减失败：'.$e->getMessage(), ['order_id' => $this->orderId]);
            throw $e;
        }
    }

    /**
     * 订单取消时回滚积分（已支付订单同时回滚获得积分）。
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function revokePoints($oldCode, $newCode): void
    {
        try {
            $reverseEarned = in_array($oldCode, [self::PAID, self::PARTIALLY_SHIPPED, self::SHIPPED, self::COMPLETED], true);
            \NiceShoply\Common\Services\Member\PointService::getInstance()->rollbackOrder($this->order, $reverseEarned);
        } catch (Throwable $e) {
            Log::error('积分回滚失败：'.$e->getMessage(), ['order_id' => $this->orderId]);
        }
    }

    /**
     * @param  $balanceType
     * @return void
     * @throws Throwable
     */
    private function handleBalance($balanceType): void
    {
        if (empty($this->order->customer_id)) {
            return;
        }

        $balanceFee = $this->order->fees()->where('code', 'balance')->first();
        if (empty($balanceFee)) {
            return;
        }

        if ($balanceType == 'redeem') {
            $type = Transaction::TYPE_CONSUMPTION;
        } else {
            $type = Transaction::TYPE_REFUND;
        }

        $data = [
            'customer_id' => $this->order->customer_id,
            'amount'      => $balanceFee->value,
            'type'        => $type,
            'comment'     => $data['comment'] ?? '',
        ];
        TransactionRepo::getInstance()->create($data);
    }

    /**
     * Add logistics information to the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addShipment($oldCode, $newCode): void
    {
        // In warehouse mode, shipments are created during checkout
        if (system_setting('warehouse_enabled', false)) {
            return;
        }

        $shipment       = $this->shipment;
        $expressCode    = $shipment['express_code'] ?? '';
        $expressCompany = $shipment['express_company'] ?? '';
        $expressNumber  = $shipment['express_number'] ?? '';
        if ($expressCode && $expressCompany && $expressNumber) {
            $orderShipment = new Order\Shipment([
                'order_id'        => $this->orderId,
                'express_code'    => $expressCode,
                'express_company' => $expressCompany,
                'express_number'  => $expressNumber,
            ]);
            $orderShipment->saveOrFail();
        }
    }

    /**
     * Add payment information to the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addPayment($oldCode, $newCode): void
    {
        if (empty($this->payment)) {
            return;
        }
        PaymentRepo::getInstance()->createOrUpdatePayment($this->orderId, $this->payment);
    }

    /**
     * Send a new order notification.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function notifyNewOrder($oldCode, $newCode): void
    {
        if (! $this->notify) {
            return;
        }
        $this->order->notifyNewOrder();
    }

    /**
     * Send an order status update notification.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function notifyUpdateOrder($oldCode, $newCode): void
    {
        if (! $this->notify) {
            return;
        }
        $this->order->notifyUpdateOrder($oldCode);
    }
}
