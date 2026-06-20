<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Booking\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Booking\Models\Booking;
use Plugin\Booking\Services\BookingService;

class BookingController extends BaseController
{
    public function services(): mixed
    {
        return json_success('ok', BookingService::getInstance()->activeServices());
    }

    public function slots(Request $request): mixed
    {
        $serviceId = (int) $request->query('service_id', 0);
        $date      = (string) $request->query('date', now()->toDateString());

        return json_success('ok', BookingService::getInstance()->availableSlots($serviceId, $date));
    }

    public function store(Request $request): mixed
    {
        try {
            $customerId = (int) token_customer_id();
            if ($customerId <= 0) {
                return json_fail(__('Booking::common.need_login'));
            }
            $booking = BookingService::getInstance()->book($customerId, $request->all());

            return json_success(__('Booking::common.booked'), $booking);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function myBookings(): mixed
    {
        $customerId = (int) token_customer_id();
        if ($customerId <= 0) {
            return json_fail(__('Booking::common.need_login'));
        }

        return json_success('ok', BookingService::getInstance()->listForCustomer($customerId));
    }

    public function cancel(int $id): mixed
    {
        try {
            $customerId = (int) token_customer_id();
            $booking = BookingService::getInstance()->setStatus($id, Booking::STATUS_CANCELLED, $customerId);

            return json_success(__('Booking::common.cancelled'), $booking);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
