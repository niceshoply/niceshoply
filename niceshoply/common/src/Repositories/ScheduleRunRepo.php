<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use NiceShoply\Common\Models\ScheduleRun;

/**
 * 计划任务执行记录仓库。
 */
class ScheduleRunRepo extends BaseRepo
{
    protected string $model = ScheduleRun::class;

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(array $data): ScheduleRun
    {
        return ScheduleRun::query()->create($data);
    }

    public function latestByCommand(string $command): ?ScheduleRun
    {
        return ScheduleRun::query()
            ->where('command', $command)
            ->orderByDesc('ran_at')
            ->first();
    }

    public function latestAny(): ?ScheduleRun
    {
        return ScheduleRun::query()->orderByDesc('ran_at')->first();
    }
}
