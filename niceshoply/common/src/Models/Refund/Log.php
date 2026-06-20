<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Refund;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Refund;

/**
 * 退款单流水模型
 */
class Log extends BaseModel
{
    protected $table = 'nice_refund_logs';

    protected $fillable = [
        'refund_id', 'from_status', 'to_status', 'comment', 'context', 'operator_id',
    ];

    protected $casts = [
        'context'     => 'array',
        'operator_id' => 'integer',
    ];

    /**
     * 所属退款单。
     *
     * @return BelongsTo
     */
    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class, 'refund_id');
    }
}
