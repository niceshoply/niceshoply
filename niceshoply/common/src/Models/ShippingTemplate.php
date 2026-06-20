<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 运费模板模型
 */
class ShippingTemplate extends BaseModel
{
    use LogsActivity;

    protected $table = 'nice_shipping_templates';

    protected $fillable = [
        'name', 'zone_id', 'calc_type', 'rules', 'free_threshold', 'priority', 'active',
    ];

    protected $casts = [
        'rules'          => 'array',
        'free_threshold' => 'decimal:4',
        'priority'       => 'integer',
        'active'         => 'boolean',
    ];

    /**
     * 所属配送区域。
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * ActivityLog 审计配置。
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'zone_id', 'calc_type', 'rules', 'free_threshold', 'priority', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "ShippingTemplate {$eventName}")
            ->useLogName('admin');
    }
}
