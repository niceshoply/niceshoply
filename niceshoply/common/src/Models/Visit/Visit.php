<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Visit;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Customer;

/**
 * 访问记录模型（会话级聚合）
 */
class Visit extends BaseModel
{
    protected $table = 'visits';

    protected $fillable = [
        'session_id',
        'customer_id',
        'ip_address',
        'user_agent',
        'country_code',
        'country_name',
        'city',
        'referrer',
        'device_type',
        'browser',
        'os',
        'locale',
        'first_visited_at',
        'last_visited_at',
    ];

    protected $casts = [
        'first_visited_at' => 'datetime',
        'last_visited_at'  => 'datetime',
    ];

    /**
     * 所属客户（登录用户）。
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 访问事件关联。
     *
     * @return HasMany
     */
    public function visitEvents(): HasMany
    {
        return $this->hasMany(VisitEvent::class, 'session_id', 'session_id');
    }

    /**
     * 页面浏览数（来自 visit_events）。
     *
     * @return int
     */
    public function getPageViewsAttribute(): int
    {
        if (array_key_exists('page_views', $this->attributes)) {
            unset($this->attributes['page_views']);
        }

        if (array_key_exists('page_views', $this->original)) {
            unset($this->original['page_views']);
        }

        if ($this->relationLoaded('visitEvents')) {
            return $this->visitEvents
                ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
                ->count();
        }

        return VisitEvent::where('session_id', $this->session_id)
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->count();
    }

    /**
     * 访问时长（秒，来自 visit_events）。
     *
     * @return int
     */
    public function getVisitDurationAttribute(): int
    {
        if (array_key_exists('visit_duration', $this->attributes)) {
            unset($this->attributes['visit_duration']);
        }

        $events = $this->relationLoaded('visitEvents')
            ? $this->visitEvents
            : VisitEvent::where('session_id', $this->session_id)->orderBy('created_at')->get();

        if ($events->count() < 2) {
            return 0;
        }

        $firstEvent = $events->first();
        $lastEvent  = $events->last();

        return (int) $lastEvent->created_at->diffInSeconds($firstEvent->created_at);
    }

    /**
     * 转化事件（取优先级最高者）。
     *
     * @return string|null
     */
    public function getConversionEventAttribute(): ?string
    {
        $priority = [
            VisitEvent::TYPE_PAYMENT_COMPLETED => 4,
            VisitEvent::TYPE_ORDER_PLACED      => 3,
            VisitEvent::TYPE_CHECKOUT_START    => 2,
            VisitEvent::TYPE_REGISTER          => 1,
        ];

        if ($this->relationLoaded('visitEvents')) {
            $events = $this->visitEvents->whereIn('event_type', array_keys($priority));
        } else {
            $events = VisitEvent::where('session_id', $this->session_id)
                ->whereIn('event_type', array_keys($priority))
                ->get();
        }

        if ($events->isEmpty()) {
            return null;
        }

        $event = $events->sortByDesc(function ($event) use ($priority) {
            return $priority[$event->event_type] ?? 0;
        })->first();

        return $event->event_type;
    }

    /**
     * 设备类型展示名。
     *
     * @return string
     */
    public function getDeviceTypeDisplayAttribute(): string
    {
        $types = [
            'desktop' => trans('console/visit.device_desktop'),
            'mobile'  => trans('console/visit.device_mobile'),
            'tablet'  => trans('console/visit.device_tablet'),
        ];

        return $types[$this->device_type] ?? (string) $this->device_type;
    }
}
