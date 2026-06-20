<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Order\Fee;
use NiceShoply\Common\Models\Order\History;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Order\Payment;
use NiceShoply\Common\Models\Order\Shipment;
use NiceShoply\Common\Notifications\OrderNewNotification;
use NiceShoply\Common\Notifications\OrderUpdateNotification;
use NiceShoply\Common\Services\CartService;
use NiceShoply\Common\Services\OrderService;
use NiceShoply\Common\Services\StateMachineService;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Throwable;

class Order extends BaseModel
{
    use LogsActivity, Notifiable;

    protected $table = 'orders';

    protected $fillable = [
        'number', 'customer_id', 'customer_group_id', 'shipping_address_id', 'billing_address_id', 'customer_name',
        'email', 'calling_code', 'telephone', 'total', 'locale', 'currency_code', 'currency_value', 'ip', 'user_agent',
        'status', 'shipping_method_code', 'shipping_method_name', 'shipping_customer_name', 'shipping_calling_code',
        'shipping_telephone', 'shipping_country', 'shipping_country_id', 'shipping_state_id', 'shipping_state',
        'shipping_city', 'shipping_address_1', 'shipping_address_2', 'shipping_zipcode', 'billing_method_code',
        'billing_method_name', 'billing_customer_name', 'billing_calling_code', 'billing_telephone', 'billing_country',
        'billing_country_id', 'billing_state_id', 'billing_state', 'billing_city', 'billing_address_1',
        'billing_address_2', 'billing_zipcode', 'comment', 'admin_note',
        'risk_score', 'risk_flags', 'is_high_risk',
        'currency_snapshot_at', 'proforma_number', 'customer_tax_id', 'destination_tax_amount',
    ];

    protected $casts = [
        'currency_snapshot_at'   => 'datetime',
        'destination_tax_amount' => 'decimal:4',
        'risk_flags'             => 'array',
        'is_high_risk'           => 'boolean',
        'risk_score'             => 'integer',
    ];

    protected $appends = [
        'total_format',
        'status_format',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'admin_note', 'comment', 'total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Order #{$this->number} {$eventName}")
            ->useLogName('admin');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id', 'id')->whereRaw('id != parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function shippingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Order items.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'order_id', 'id');
    }

    /**
     * Order fees.
     *
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'order_id', 'id');
    }

    /**
     * Order histories.
     *
     * @return HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class, 'order_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'order_id', 'id');
    }

    /**
     * Order payments.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    /**
     * Calculate order subtotal.
     *
     * @return float
     */
    public function calcSubtotal(): float
    {
        $decimal = currency_decimal_place($this->currency_code);

        return round($this->items->sum('subtotal'), $decimal);
    }

    /**
     * Calculate order total.
     *
     * @return float
     */
    public function calcTotal(): float
    {
        $decimal = currency_decimal_place($this->currency_code);

        return round($this->fees->sum('value'), $decimal);
    }

    /**
     * Format total by currency.
     *
     * @return string
     */
    public function getTotalFormatAttribute(): string
    {
        return currency_format($this->total, $this->currency_code, $this->currency_value);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getStatusColorAttribute(): string
    {
        $statusCode = $this->status;
        if ($statusCode == null) {
            return '';
        }

        if ($statusCode == StateMachineService::UNPAID) {
            return 'warning';
        } elseif (in_array($statusCode, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            return 'danger';
        } else {
            return 'success';
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getStatusFormatAttribute(): string
    {
        $statusCode = $this->status;
        if ($statusCode == null) {
            return '';
        }

        $statusMap = array_column(StateMachineService::getAllStatuses(), 'name', 'status');

        return $statusMap[$statusCode] ?? '';
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function addToCart(): void
    {
        CartService::getInstance($this->customer_id)->addOrderToCart($this);
    }

    /**
     * @return Order
     * @throws Throwable
     */
    public function reorder(): Order
    {
        return OrderService::getInstance($this->id)->reorder();
    }

    /**
     * Send a new order notification.
     *
     * @return void
     */
    public function notifyNewOrder(): void
    {
        try {
            $this->notify(new OrderNewNotification($this));
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            Log::error($th->getTraceAsString());
        }
    }

    /**
     * Send an order status update notification.
     *
     * @param  $fromCode
     * @return void
     */
    public function notifyUpdateOrder($fromCode): void
    {
        try {
            $this->notify(new OrderUpdateNotification($this, $fromCode));
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            Log::error($th->getTraceAsString());
        }
    }
}
