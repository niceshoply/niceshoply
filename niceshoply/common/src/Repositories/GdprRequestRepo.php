<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\GdprRequest;

/**
 * GDPR 申请数据访问层。
 */
class GdprRequestRepo extends BaseRepo
{
    protected string $model = GdprRequest::class;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'type', 'type' => 'select', 'label' => trans('console/gdpr.type'), 'options' => [
                ['code' => GdprRequest::TYPE_EXPORT, 'label' => trans('console/gdpr.type_export')],
                ['code' => GdprRequest::TYPE_DELETE, 'label' => trans('console/gdpr.type_delete')],
            ], 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'status', 'type' => 'select', 'label' => trans('console/gdpr.status'), 'options' => [
                ['code' => GdprRequest::STATUS_PENDING, 'label' => trans('console/gdpr.status_pending')],
                ['code' => GdprRequest::STATUS_PROCESSING, 'label' => trans('console/gdpr.status_processing')],
                ['code' => GdprRequest::STATUS_COMPLETED, 'label' => trans('console/gdpr.status_completed')],
                ['code' => GdprRequest::STATUS_FAILED, 'label' => trans('console/gdpr.status_failed')],
            ], 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = GdprRequest::query()->with('customer');

        if (! empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $builder->where('customer_id', (int) $filters['customer_id']);
        }

        return $builder->orderByDesc('id');
    }

    public function findPendingExport(int $customerId): ?GdprRequest
    {
        return GdprRequest::query()
            ->where('customer_id', $customerId)
            ->where('type', GdprRequest::TYPE_EXPORT)
            ->whereIn('status', [GdprRequest::STATUS_PENDING, GdprRequest::STATUS_PROCESSING])
            ->first();
    }

    public function findPendingDelete(int $customerId): ?GdprRequest
    {
        return GdprRequest::query()
            ->where('customer_id', $customerId)
            ->where('type', GdprRequest::TYPE_DELETE)
            ->whereIn('status', [GdprRequest::STATUS_PENDING, GdprRequest::STATUS_PROCESSING])
            ->first();
    }
}
