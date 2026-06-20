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
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Customer;

/**
 * 访问事件模型（用于转化漏斗分析）
 */
class VisitEvent extends BaseModel
{
    protected $table = 'visit_events';

    protected $fillable = [
        'session_id',
        'event_type',
        'event_data',
        'customer_id',
        'ip_address',
        'page_url',
        'referrer',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    /** 事件类型常量 */
    public const TYPE_PAGE_VIEW = 'page_view';

    public const TYPE_PRODUCT_VIEW = 'product_view';

    public const TYPE_ADD_TO_CART = 'add_to_cart';

    public const TYPE_CHECKOUT_START = 'checkout_start';

    public const TYPE_ORDER_PLACED = 'order_placed';

    public const TYPE_PAYMENT_COMPLETED = 'payment_completed';

    public const TYPE_REGISTER = 'register';

    public const TYPE_HOME_VIEW = 'home_view';

    public const TYPE_CATEGORY_VIEW = 'category_view';

    public const TYPE_SEARCH = 'search';

    public const TYPE_CART_VIEW = 'cart_view';

    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    /**
     * 所属客户。
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 事件类型展示名。
     *
     * @return string
     */
    public function getEventTypeDisplayAttribute(): string
    {
        $types = [
            self::TYPE_PAGE_VIEW         => trans('console/visit.event_page_view'),
            self::TYPE_PRODUCT_VIEW      => trans('console/visit.event_product_view'),
            self::TYPE_ADD_TO_CART       => trans('console/visit.event_add_to_cart'),
            self::TYPE_CHECKOUT_START    => trans('console/visit.event_checkout_start'),
            self::TYPE_ORDER_PLACED      => trans('console/visit.event_order_placed'),
            self::TYPE_PAYMENT_COMPLETED => trans('console/visit.event_payment_completed'),
            self::TYPE_REGISTER          => trans('console/visit.event_register'),
            self::TYPE_HOME_VIEW         => trans('console/visit.event_home_view'),
            self::TYPE_CATEGORY_VIEW     => trans('console/visit.event_category_view'),
            self::TYPE_SEARCH            => trans('console/visit.event_search'),
            self::TYPE_CART_VIEW         => trans('console/visit.event_cart_view'),
            self::TYPE_ORDER_CANCELLED   => trans('console/visit.event_order_cancelled'),
        ];

        return $types[$this->event_type] ?? (string) $this->event_type;
    }
}
