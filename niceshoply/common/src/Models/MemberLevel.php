<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use NiceShoply\Common\Traits\Translatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 会员等级模型
 */
class MemberLevel extends BaseModel
{
    use LogsActivity;
    use Translatable;

    protected $table = 'nice_member_levels';

    protected $fillable = [
        'name', 'threshold_type', 'threshold_value', 'discount_percent',
        'free_shipping', 'priority', 'active',
    ];

    protected $casts = [
        'threshold_value'  => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'free_shipping'    => 'boolean',
        'priority'         => 'integer',
        'active'           => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'threshold_type', 'threshold_value', 'discount_percent', 'active'])
            ->logOnlyDirty()
            ->useLogName('admin');
    }

    /**
     * 多语言展示标题。
     */
    public function getLabelAttribute(): string
    {
        return $this->translate('label', $this->name);
    }
}
