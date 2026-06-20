<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Booking\Models;

use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    protected $table = 'booking_services';

    protected $guarded = [];

    protected $casts = [
        'price'             => 'float',
        'duration_min'      => 'integer',
        'slot_interval_min' => 'integer',
        'capacity'          => 'integer',
        'is_active'         => 'boolean',
    ];

    public function openWeekdaysArray(): array
    {
        return array_filter(array_map('intval', explode(',', (string) $this->open_weekdays)));
    }
}
