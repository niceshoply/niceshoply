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
use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Models\Refund\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 退款单模型
 *
 * @property int $id
 * @property string $number
 * @property int $order_id
 * @property int|null $order_return_id
 * @property int $customer_id
 * @property string $amount
 * @property string $currency_code
 * @property string $currency_value
 * @property string $method
 * @property string $status
 * @property string|null $gateway
 * @property string|null $gateway_ref
 * @property string|null $reason
 * @property int $operator_id
 * @property \Illuminate\Support\Carbon|null $processed_at
 */
class Refund extends BaseModel
{
    use LogsActivity;

    protected $table = 'nice_refunds';

    protected $fillable = [
        'number', 'order_id', 'order_return_id', 'customer_id', 'amount', 'currency_code',
        'currency_value', 'method', 'status', 'gateway', 'gateway_ref', 'reason',
        'operator_id', 'processed_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:4',
        'currency_value' => 'decimal:8',
        'customer_id'    => 'integer',
        'operator_id'    => 'integer',
        'processed_at'   => 'datetime',
    ];

    /**
     * 退款方式：原路退回。
     */
    public const METHOD_ORIGINAL = 'original';

    /**
     * 退款方式：退回钱包余额。
     */
    public const METHOD_BALANCE = 'balance';

    /**
     * 退款方式：人工线下退款。
     */
    public const METHOD_MANUAL = 'manual';

    public const METHODS = [
        self::METHOD_ORIGINAL,
        self::METHOD_BALANCE,
        self::METHOD_MANUAL,
    ];

    /**
     * ActivityLog 审计配置。
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'amount', 'method', 'gateway_ref'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Refund #{$this->number} {$eventName}")
            ->useLogName('admin');
    }

    /**
     * 所属订单。
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 关联退货单（可空）。
     *
     * @return BelongsTo
     */
    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    /**
     * 关联客户。
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 退款流水。
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'refund_id')->orderByDesc('id');
    }

    /**
     * 状态展示文案。
     *
     * @return string
     */
    public function getStatusFormatAttribute(): string
    {
        return trans('common/refund.status_'.$this->status);
    }

    /**
     * 状态颜色（用于后台 badge）。
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return [
            'pending'    => 'secondary',
            'processing' => 'warning',
            'succeeded'  => 'success',
            'failed'     => 'danger',
            'cancelled'  => 'dark',
        ][$this->status] ?? 'secondary';
    }
}
