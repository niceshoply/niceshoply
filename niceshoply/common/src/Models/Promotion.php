<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use NiceShoply\Common\Traits\Translatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 促销活动模型（多语言）
 *
 * 通过 Translatable 关联 nice_promotion_translations（label/description）。
 * 折扣规则由 condition_type/conditions（命中条件）与 action_type/actions（优惠动作）描述，
 * 由 PromotionService 解析并经结账 feeList 注入金额闭环。
 */
class Promotion extends BaseModel
{
    use LogsActivity;
    use Translatable;

    protected $table = 'nice_promotions';

    protected $fillable = [
        'name',
        'scope',
        'condition_type',
        'conditions',
        'action_type',
        'actions',
        'priority',
        'exclusive',
        'usage_limit',
        'used_count',
        'per_customer_limit',
        'customer_group_ids',
        'starts_at',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'conditions'         => 'array',
        'actions'            => 'array',
        'customer_group_ids' => 'array',
        'exclusive'          => 'boolean',
        'active'             => 'boolean',
        'priority'           => 'integer',
        'usage_limit'        => 'integer',
        'used_count'         => 'integer',
        'per_customer_limit' => 'integer',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
    ];

    /**
     * ActivityLog 审计配置。
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'action_type', 'active', 'usage_limit', 'used_count'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Promotion {$eventName}")
            ->useLogName('admin');
    }

    /**
     * 展示文案（当前语言，带回退）。
     *
     * @return string
     */
    public function getLabelAttribute(): string
    {
        return $this->fallbackName('label') ?: $this->name;
    }
}
