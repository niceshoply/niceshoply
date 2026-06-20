<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\OrderReturn;
use NiceShoply\Common\Services\ReturnStateService;
use Throwable;

class OrderReturnRepo extends BaseRepo
{
    /**
     * @return array[]
     * @throws \Exception
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'number', 'type' => 'input', 'label' => trans('common/rma.return_number')],
            ['name' => 'order_number', 'type' => 'input', 'label' => trans('common/rma.order_reference')],
            ['name' => 'customer_name', 'type' => 'input', 'label' => trans('common/rma.customer_name')],
            ['name' => 'customer_email', 'type' => 'input', 'label' => trans('common/rma.customer_email')],
            ['name' => 'product_name', 'type' => 'input', 'label' => trans('common/rma.product_name')],
            [
                'name'    => 'status', 'type' => 'select', 'label' => trans('front/return.status'),
                'options' => array_map(static fn ($status) => [
                    'status' => $status,
                    'name'   => trans("common/rma.$status"),
                ], ReturnStateService::ORDER_STATUS),
                'options_key' => 'status', 'options_label' => 'name',
            ],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('front/return.created_at')],
        ];
    }

    /**
     * Count of order returns grouped by status, with a total entry.
     *
     * @param  array  $filters
     * @return array
     */
    public function statusCounts(array $filters = []): array
    {
        $base = $this->builder(array_diff_key($filters, ['status' => true]));

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $result = ['' => (clone $base)->count()];
        foreach (ReturnStateService::ORDER_STATUS as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }

    protected string $model = OrderReturn::class;

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $returnData = $this->handleData($data);
        $returnItem = new OrderReturn($returnData);
        $returnItem->saveOrFail();

        return $returnItem;
    }

    /**
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder = OrderReturn::query();
        $number  = $filters['number'] ?? 0;
        if ($number) {
            $builder->where('number', 'like', "%$number%");
        }

        $orderNumber = $filters['order_number'] ?? '';
        if ($orderNumber) {
            $builder->where('order_number', 'like', "%$orderNumber%");
        }

        $productName = $filters['product_name'] ?? '';
        if ($productName) {
            $builder->where('product_name', 'like', "%$productName%");
        }

        $customerName = $filters['customer_name'] ?? '';
        if ($customerName) {
            $builder->whereHas('customer', function ($query) use ($customerName) {
                $query->where('name', 'like', "%$customerName%");
            });
        }

        $customerEmail = $filters['customer_email'] ?? '';
        if ($customerEmail) {
            $builder->whereHas('customer', function ($query) use ($customerEmail) {
                $query->where('email', 'like', "%$customerEmail%");
            });
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->whereDate('created_at', '>=', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->whereDate('created_at', '<=', $createdEnd);
        }

        $builder = fire_hook_filter('repo.order_return.builder', $builder);

        return $builder;
    }

    /**
     * Generate order return number.
     *
     * @return string
     */
    private function generateReturnNumber(): string
    {
        $number = 'RMA-'.date('Ymd').rand(10000, 99999);
        if (! $this->builder(['number' => $number])->exists()) {
            return $number;
        }

        return $this->generateReturnNumber();
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        $orderItemID = $data['order_item_id'];
        $orderItem   = Item::query()->findOrFail($orderItemID);
        $originOrder = $orderItem->order;

        return [
            'customer_id'   => $data['customer_id'],
            'order_id'      => $orderItem->order_id,
            'order_item_id' => $orderItemID,
            'product_id'    => $orderItem->product_id,
            'number'        => $this->generateReturnNumber(),
            'order_number'  => $originOrder->number,
            'product_name'  => $orderItem->name,
            'product_sku'   => $orderItem->product_sku,
            'opened'        => $data['opened'] ?? true,
            'quantity'      => $data['quantity'] ?? 1,
            'comment'       => $data['comment'] ?? '',
            'reason_id'     => $data['reason_id'] ?? 0,
            'status'        => ReturnStateService::CREATED,
        ];
    }
}
