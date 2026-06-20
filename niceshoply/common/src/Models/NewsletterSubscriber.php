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

/**
 * Newsletter 订阅者模型
 */
class NewsletterSubscriber extends BaseModel
{
    protected $table = 'newsletter_subscribers';

    /** 状态常量 */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_BOUNCED = 'bounced';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_BOUNCED,
    ];

    /** 来源常量 */
    public const SOURCE_FOOTER = 'footer';

    public const SOURCE_POPUP = 'popup';

    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCES = [
        self::SOURCE_FOOTER,
        self::SOURCE_POPUP,
        self::SOURCE_CHECKOUT,
        self::SOURCE_MANUAL,
    ];

    protected $fillable = [
        'email',
        'name',
        'customer_id',
        'status',
        'source',
        'subscribed_at',
        'unsubscribed_at',
        'notes',
    ];

    protected $casts = [
        'subscribed_at'   => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * 所属客户（已注册用户）。
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 是否为活跃订阅。
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 订阅。
     *
     * @return void
     */
    public function subscribe(): void
    {
        $this->status          = self::STATUS_ACTIVE;
        $this->subscribed_at   = now();
        $this->unsubscribed_at = null;
        $this->save();
    }

    /**
     * 退订。
     *
     * @return void
     */
    public function unsubscribe(): void
    {
        $this->status          = self::STATUS_UNSUBSCRIBED;
        $this->unsubscribed_at = now();
        $this->save();
    }
}
