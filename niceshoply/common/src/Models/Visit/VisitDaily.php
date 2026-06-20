<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Visit;

use NiceShoply\Common\Models\BaseModel;

/**
 * 每日访问统计模型
 */
class VisitDaily extends BaseModel
{
    protected $table = 'visit_daily';

    protected $primaryKey = 'date';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'date',
        'pv',
        'uv',
        'ip',
        'new_visitors',
        'bounces',
        'avg_duration',
        'desktop_pv',
        'mobile_pv',
        'tablet_pv',
    ];

    /**
     * 全设备总 PV。
     *
     * @return int
     */
    public function getTotalPvAttribute(): int
    {
        if (array_key_exists('total_pv', $this->attributes)) {
            return (int) $this->attributes['total_pv'];
        }

        return ($this->desktop_pv ?? 0) + ($this->mobile_pv ?? 0) + ($this->tablet_pv ?? 0);
    }
}
