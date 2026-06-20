<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\StockTransfer;

class StockTransferRepo extends BaseRepo
{
    protected string $model = StockTransfer::class;

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'number', 'type' => 'input', 'label' => trans('console/warehouse.transfer_number')],
            [
                'name'    => 'status', 'type' => 'select', 'label' => trans('console/warehouse.status'),
                'options' => collect(StockTransfer::STATUSES)->map(fn ($s) => [
                    'value' => $s, 'label' => trans("console/warehouse.transfer_status_{$s}"),
                ])->toArray(),
            ],
        ];
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('id')->paginate();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = StockTransfer::query()->with(['fromWarehouse', 'toWarehouse', 'items']);
        $filters = array_merge($this->filters, $filters);

        $number = $filters['number'] ?? '';
        if ($number) {
            $builder->where('number', $number);
        }

        $status = $filters['status'] ?? '';
        if ($status && in_array($status, StockTransfer::STATUSES)) {
            $builder->where('status', $status);
        }

        $fromWarehouseId = $filters['from_warehouse_id'] ?? 0;
        if ($fromWarehouseId) {
            $builder->where('from_warehouse_id', $fromWarehouseId);
        }

        $toWarehouseId = $filters['to_warehouse_id'] ?? 0;
        if ($toWarehouseId) {
            $builder->where('to_warehouse_id', $toWarehouseId);
        }

        return $builder;
    }

    /**
     * Generate a unique transfer number.
     *
     * @return string
     */
    public static function generateTransferNumber(): string
    {
        $number = 'T'.date('Ymd').rand(10000, 99999);
        if (! StockTransfer::query()->where('number', $number)->exists()) {
            return $number;
        }

        return self::generateTransferNumber();
    }
}
