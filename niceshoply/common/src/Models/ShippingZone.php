<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 配送区域模型
 */
class ShippingZone extends BaseModel
{
    use LogsActivity;

    protected $table = 'nice_shipping_zones';

    protected $fillable = [
        'name', 'country_ids', 'state_ids', 'priority', 'active',
    ];

    protected $casts = [
        'country_ids' => 'array',
        'state_ids'   => 'array',
        'priority'    => 'integer',
        'active'      => 'boolean',
    ];

    /**
     * 区域下的运费模板。
     *
     * @return HasMany
     */
    public function templates(): HasMany
    {
        return $this->hasMany(ShippingTemplate::class, 'zone_id');
    }

    /**
     * 判断目的地（国家/省州）是否落入本区域。
     *
     * @param  int  $countryId
     * @param  int  $stateId
     * @return bool
     */
    public function matches(int $countryId, int $stateId): bool
    {
        $countryIds = array_map('intval', $this->country_ids ?? []);
        if (! empty($countryIds) && ! in_array($countryId, $countryIds, true)) {
            return false;
        }

        $stateIds = array_map('intval', $this->state_ids ?? []);
        if (! empty($stateIds) && ! in_array($stateId, $stateIds, true)) {
            return false;
        }

        return true;
    }

    /**
     * ActivityLog 审计配置。
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'country_ids', 'state_ids', 'priority', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "ShippingZone {$eventName}")
            ->useLogName('admin');
    }
}
