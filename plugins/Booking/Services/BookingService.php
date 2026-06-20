<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Booking\Services;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Plugin\Booking\Models\Booking;
use Plugin\Booking\Models\BookingService as ServiceModel;

class BookingService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function activeServices()
    {
        return ServiceModel::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /**
     * 生成指定服务在某天的可约时段（含剩余容量）。
     *
     * @return array<int, array{time:string, capacity:int, booked:int, available:int}>
     */
    public function availableSlots(int $serviceId, string $date): array
    {
        /** @var ServiceModel|null $service */
        $service = ServiceModel::query()->where('is_active', true)->find($serviceId);
        if (! $service) {
            return [];
        }

        $day = Carbon::parse($date);
        // Carbon: dayOfWeekIso 1=Mon..7=Sun
        if (! in_array($day->dayOfWeekIso, $service->openWeekdaysArray(), true)) {
            return [];
        }

        $start = Carbon::parse($date.' '.$service->open_time);
        $end   = Carbon::parse($date.' '.$service->close_time);
        $step  = max(15, (int) $service->slot_interval_min);

        // 统计当天各时段已约数量（不含取消）
        $bookedMap = Booking::query()
            ->where('service_id', $serviceId)
            ->whereDate('booking_date', $day->toDateString())
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->selectRaw('booking_time, SUM(people) as cnt')
            ->groupBy('booking_time')
            ->pluck('cnt', 'booking_time')
            ->toArray();

        $slots  = [];
        $cursor = $start->copy();
        while ($cursor->copy()->addMinutes((int) $service->duration_min)->lte($end)) {
            $time   = $cursor->format('H:i');
            $booked = (int) ($bookedMap[$time] ?? 0);
            $cap    = (int) $service->capacity;
            $slots[] = [
                'time'      => $time,
                'capacity'  => $cap,
                'booked'    => $booked,
                'available' => max($cap - $booked, 0),
            ];
            $cursor->addMinutes($step);
        }

        return $slots;
    }

    /**
     * 创建预约（容量校验，防超约）。
     *
     * @throws RuntimeException
     */
    public function book(int $customerId, array $data): Booking
    {
        $serviceId = (int) ($data['service_id'] ?? 0);
        $date      = (string) ($data['booking_date'] ?? '');
        $time      = (string) ($data['booking_time'] ?? '');
        $people    = max(1, (int) ($data['people'] ?? 1));

        /** @var ServiceModel|null $service */
        $service = ServiceModel::query()->where('is_active', true)->find($serviceId);
        if (! $service) {
            throw new RuntimeException(__('Booking::common.service_not_found'));
        }
        if ($date === '' || $time === '') {
            throw new RuntimeException(__('Booking::common.need_slot'));
        }

        // 提前预约天数限制
        $advance = (int) plugin_setting('booking', 'advance_days', 0);
        if ($advance > 0 && Carbon::parse($date)->gt(Carbon::today()->addDays($advance))) {
            throw new RuntimeException(__('Booking::common.too_far', ['days' => $advance]));
        }
        if (Carbon::parse($date.' '.$time)->isPast()) {
            throw new RuntimeException(__('Booking::common.slot_past'));
        }

        return DB::transaction(function () use ($service, $serviceId, $customerId, $date, $time, $people, $data) {
            $booked = (int) Booking::query()
                ->where('service_id', $serviceId)
                ->whereDate('booking_date', $date)
                ->where('booking_time', $time)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->lockForUpdate()
                ->sum('people');

            if ($booked + $people > (int) $service->capacity) {
                throw new RuntimeException(__('Booking::common.slot_full'));
            }

            return Booking::query()->create([
                'service_id'    => $serviceId,
                'customer_id'   => $customerId,
                'customer_name' => $data['customer_name'] ?? '',
                'phone'         => $data['phone'] ?? '',
                'booking_date'  => $date,
                'booking_time'  => $time,
                'people'        => $people,
                'status'        => Booking::STATUS_PENDING,
                'remark'        => $data['remark'] ?? '',
            ]);
        });
    }

    public function listForCustomer(int $customerId)
    {
        return Booking::query()
            ->with('service')
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->get();
    }

    public function setStatus(int $id, string $status, ?int $customerId = null): Booking
    {
        if (! in_array($status, [
            Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED,
            Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED,
        ], true)) {
            throw new RuntimeException(__('Booking::common.invalid_status'));
        }

        $query = Booking::query();
        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        }

        /** @var Booking $booking */
        $booking = $query->findOrFail($id);
        $booking->status = $status;
        $booking->save();

        return $booking;
    }
}
