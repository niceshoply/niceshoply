<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

/**
 * 系统备份记录。
 */
class Backup extends BaseModel
{
    public const TYPE_FULL = 'full';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'nice_backups';

    protected $fillable = [
        'type', 'status', 'file_path', 'file_size', 'checksum', 'triggered_by',
        'admin_id', 'error_message', 'metadata', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'file_size'    => 'integer',
        'admin_id'     => 'integer',
        'metadata'     => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
}
