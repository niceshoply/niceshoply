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
 * 每日转化统计模型（转化漏斗指标）
 */
class ConversionDaily extends BaseModel
{
    protected $table = 'conversion_daily';

    protected $primaryKey = 'date';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'date',
        'home_views',
        'category_views',
        'product_views',
        'add_to_carts',
        'checkout_starts',
        'order_placed',
        'payment_completed',
        'registers',
        'searches',
        'cart_views',
        'order_cancelled',
        'cart_to_checkout_rate',
        'checkout_to_order_rate',
        'order_to_payment_rate',
        'overall_conversion_rate',
    ];

    /**
     * 加购 → 结账 转化率（百分比）。
     *
     * @return float
     */
    public function getCartToCheckoutPercentAttribute(): float
    {
        return $this->cart_to_checkout_rate / 100;
    }

    /**
     * 结账 → 下单 转化率（百分比）。
     *
     * @return float
     */
    public function getCheckoutToOrderPercentAttribute(): float
    {
        return $this->checkout_to_order_rate / 100;
    }

    /**
     * 下单 → 支付 转化率（百分比）。
     *
     * @return float
     */
    public function getOrderToPaymentPercentAttribute(): float
    {
        return $this->order_to_payment_rate / 100;
    }

    /**
     * 整体转化率（浏览 → 支付，百分比）。
     *
     * @return float
     */
    public function getOverallConversionPercentAttribute(): float
    {
        return $this->overall_conversion_rate / 100;
    }
}
