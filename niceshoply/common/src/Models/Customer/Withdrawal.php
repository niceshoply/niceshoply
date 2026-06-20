<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Customer;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Customer;

class Withdrawal extends BaseModel
{
    protected $table = 'customer_withdrawals';

    protected $fillable = [
        'customer_id', 'amount', 'account_type', 'account_number', 'bank_name',
        'bank_account', 'status', 'comment', 'admin_comment',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUS_PAID = 'paid';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_PAID,
    ];

    const ACCOUNT_TYPE_BANK = 'bank';

    const ACCOUNT_TYPE_ALIPAY = 'alipay';

    const ACCOUNT_TYPE_WECHAT = 'wechat';

    const ACCOUNT_TYPES = [
        self::ACCOUNT_TYPE_BANK,
        self::ACCOUNT_TYPE_ALIPAY,
        self::ACCOUNT_TYPE_WECHAT,
    ];

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return string
     */
    public function getStatusFormatAttribute(): string
    {
        return trans('front/withdrawal.'.$this->status);
    }

    /**
     * @return string
     */
    public function getAccountTypeFormatAttribute(): string
    {
        return trans('front/withdrawal.'.$this->account_type);
    }
}
