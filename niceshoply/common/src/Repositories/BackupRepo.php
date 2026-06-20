<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\Backup;

/**
 * 备份记录数据访问层。
 */
class BackupRepo extends BaseRepo
{
    protected string $model = Backup::class;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Backup::query();

        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderByDesc('id');
    }

    public function latestCompleted(): ?Backup
    {
        return Backup::query()
            ->where('status', Backup::STATUS_COMPLETED)
            ->orderByDesc('id')
            ->first();
    }
}
