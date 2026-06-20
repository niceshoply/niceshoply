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
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\OrderRepo;
use Throwable;

class OrderService
{
    private Order $order;

    /**
     * @param  int  $orderID
     */
    public function __construct(int $orderID)
    {
        $this->order = Order::query()->findOrFail($orderID);
    }

    /**
     * @param  int  $orderID
     * @return static
     */
    public static function getInstance(int $orderID): static
    {
        return new static($orderID);
    }

    /**
     * Confirm checkout and place order.
     *
     * @return mixed
     * @throws Exception|Throwable
     */
    public function reorder(): mixed
    {
        DB::beginTransaction();

        try {
            $this->order->load(['items', 'fees']);
            $order = $this->order->replicate();

            $order->number = OrderRepo::generateOrderNumber();
            $order->saveOrFail();

            foreach ($this->order->items as $item) {
                $newItem           = $item->replicate();
                $newItem->order_id = $order->id;
                $newItem->saveOrFail();
            }

            foreach ($this->order->fees as $fee) {
                $newFee           = $fee->replicate();
                $newFee->order_id = $order->id;
                $newFee->saveOrFail();
            }

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
