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
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\OrderReturn\History;
use NiceShoply\Common\Models\OrderReturn\Payment;
use NiceShoply\Common\Notifications\OrderReturnUpdateNotification;
use Throwable;

class OrderReturn extends BaseModel
{
    use Notifiable;

    protected $table = 'order_returns';

    protected $fillable = [
        'customer_id', 'order_id', 'order_item_id', 'product_id', 'number', 'order_number', 'product_name', 'product_sku',
        'opened', 'quantity', 'comment', 'reason_id', 'status',
    ];

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Order\Item::class, 'order_item_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'reason_id', 'id');
    }

    /**
     * @return string
     */
    public function getReasonNameAttribute(): string
    {
        return $this->reason?->name ?? '';
    }

    /**
     * @return HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class, 'order_return_id', 'id')->orderByDesc('id');
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_return_id', 'id')->orderByDesc('id');
    }

    /**
     * 关联退款单（新退款闭环）。
     *
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'order_return_id', 'id')->orderByDesc('id');
    }

    /**
     * @return string
     */
    public function getOpenedFormatAttribute(): string
    {
        return $this->opened ? trans('front/common.yes') : trans('front/common.no');
    }

    /**
     * Total refunded amount of this return.
     *
     * @return float
     */
    public function getRefundTotalAttribute(): float
    {
        $fromRefunds = (float) $this->refunds()->where('status', 'succeeded')->sum('amount');
        if ($fromRefunds > 0) {
            return $fromRefunds;
        }

        return (float) $this->payments->sum('amount');
    }

    /**
     * Customer email used as the notification route.
     *
     * @return string
     */
    public function getEmailAttribute(): string
    {
        return (string) ($this->customer?->email ?? '');
    }

    /**
     * Customer display name.
     *
     * @return string
     */
    public function getCustomerNameAttribute(): string
    {
        return (string) ($this->customer?->name ?? '');
    }

    /**
     * Send a status update notification for this return.
     *
     * @param  string|null  $fromCode
     * @return void
     */
    public function notifyReturnUpdate(?string $fromCode = ''): void
    {
        if (! $this->email) {
            return;
        }
        try {
            $this->notify(new OrderReturnUpdateNotification($this, (string) $fromCode));
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            Log::error($th->getTraceAsString());
        }
    }

    /**
     * @return string
     */
    public function getStatusFormatAttribute(): string
    {
        return trans('common/rma.'.$this->status);
    }

    /**
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        $statusCode = $this->status;
        if ($statusCode == null) {
            return '';
        }
        $map = self::statusColorMap();

        return $map[$statusCode] ?? 'secondary';
    }

    /**
     * Get status color map.
     *
     * @return array
     */
    private static function statusColorMap(): array
    {
        return [
            \NiceShoply\Common\Services\ReturnStateService::CREATED   => 'secondary',
            \NiceShoply\Common\Services\ReturnStateService::PENDING   => 'warning',
            \NiceShoply\Common\Services\ReturnStateService::REFUNDED  => 'info',
            \NiceShoply\Common\Services\ReturnStateService::RETURNED  => 'success',
            \NiceShoply\Common\Services\ReturnStateService::CANCELLED => 'danger',
        ];
    }
}
