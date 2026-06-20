<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

/**
 * 计划任务执行记录。
 */
class ScheduleRun extends BaseModel
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_MANUAL = 'manual';

    protected $table = 'nice_schedule_runs';

    protected $fillable = [
        'command', 'expression', 'status', 'duration_ms', 'output', 'error_message', 'ran_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'ran_at'      => 'datetime',
    ];
}
