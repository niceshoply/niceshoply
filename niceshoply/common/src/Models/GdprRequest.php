<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * GDPR 数据导出/删除申请。
 */
class GdprRequest extends BaseModel
{
    use LogsActivity;

    public const TYPE_EXPORT = 'export';

    public const TYPE_DELETE = 'delete';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'nice_gdpr_requests';

    protected $fillable = [
        'customer_id', 'type', 'status', 'file_path', 'error_message', 'ip', 'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * ActivityLog 审计配置。
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'status', 'error_message'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "GdprRequest {$eventName}")
            ->useLogName('admin');
    }
}
