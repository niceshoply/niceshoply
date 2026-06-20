<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\PointLog;

/**
 * 积分流水数据访问层。
 */
class PointLogRepo extends BaseRepo
{
    protected string $model = PointLog::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'customer_id', 'type' => 'input', 'label' => trans('console/point.customer_id')],
            ['name' => 'type', 'type' => 'select', 'label' => trans('console/point.type'), 'options' => self::getTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'source', 'type' => 'input', 'label' => trans('console/point.source')],
        ];
    }

    /**
     * 流水类型选项。
     *
     * @return array<int, array<string, string>>
     */
    public static function getTypeOptions(): array
    {
        return [
            ['code' => PointLog::TYPE_EARN, 'label' => trans('console/point.type_earn')],
            ['code' => PointLog::TYPE_SPEND, 'label' => trans('console/point.type_spend')],
            ['code' => PointLog::TYPE_EXPIRE, 'label' => trans('console/point.type_expire')],
            ['code' => PointLog::TYPE_ADJUST, 'label' => trans('console/point.type_adjust')],
        ];
    }

    /**
     * 后台列表查询。
     *
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = PointLog::query()->with('customer');

        if (! empty($filters['customer_id'])) {
            $builder->where('customer_id', (int) $filters['customer_id']);
        }

        if (! empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (! empty($filters['source'])) {
            $builder->where('source', 'like', '%'.$filters['source'].'%');
        }

        return $builder->orderByDesc('id');
    }

    /**
     * 是否已存在指定来源的流水（幂等校验）。
     */
    public function existsForReference(int $customerId, string $type, string $source, int $referenceId): bool
    {
        return PointLog::query()
            ->where('customer_id', $customerId)
            ->where('type', $type)
            ->where('source', $source)
            ->where('reference_id', $referenceId)
            ->exists();
    }
}
