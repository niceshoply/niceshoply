<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * URL 重定向规则。
 */
class Redirect extends BaseModel
{
    use LogsActivity;

    protected $table = 'nice_redirects';

    protected $fillable = [
        'source_path', 'target_path', 'status_code', 'hits', 'active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits'        => 'integer',
        'active'      => 'boolean',
    ];

    /**
     * ActivityLog 审计配置。
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['source_path', 'target_path', 'status_code', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Redirect {$eventName}")
            ->useLogName('admin');
    }
}
