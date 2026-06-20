<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReturnLogistics\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugin\ReturnLogistics\Models\ReturnAddress;
use Plugin\ReturnLogistics\Models\ReturnShipment;

class ReturnLogisticsService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('return_logistics', 'enabled', true);
    }

    public function defaultAddress(): ?ReturnAddress
    {
        return ReturnAddress::query()->where('is_active', true)->orderByDesc('id')->first();
    }

    /**
     * 为售后单创建退货寄回指引。
     */
    public function createForAftersale(int $aftersaleId, ?int $addressId = null): ReturnShipment
    {
        if (! Schema::hasTable('aftersale_requests')) {
            throw new Exception(__('ReturnLogistics::common.no_aftersale'));
        }

        $aftersale = DB::table('aftersale_requests')->where('id', $aftersaleId)->first();
        if (! $aftersale) {
            throw new Exception(__('ReturnLogistics::common.invalid_aftersale'));
        }

        $addr = $addressId
            ? ReturnAddress::query()->whereKey($addressId)->where('is_active', true)->first()
            : $this->defaultAddress();

        if (! $addr) {
            throw new Exception(__('ReturnLogistics::common.no_address'));
        }

        return ReturnShipment::query()->updateOrCreate(
            ['aftersale_id' => $aftersaleId],
            [
                'order_id'          => (int) ($aftersale->order_id ?? 0),
                'order_number'      => (string) ($aftersale->order_number ?? ''),
                'customer_id'       => (int) ($aftersale->customer_id ?? 0),
                'return_address_id' => $addr->id,
                'status'            => 'pending',
            ]
        );
    }

    /**
     * 客户提交回寄运单号。
     */
    public function submitTracking(int $aftersaleId, int $customerId, string $shipperCode, string $trackingNo): ReturnShipment
    {
        $ship = ReturnShipment::query()->where('aftersale_id', $aftersaleId)->first();
        if (! $ship || ($ship->customer_id && $ship->customer_id !== $customerId)) {
            throw new Exception(__('ReturnLogistics::common.invalid_aftersale'));
        }

        $ship->update([
            'shipper_code' => strtoupper(trim($shipperCode)),
            'tracking_no'  => trim($trackingNo),
            'status'       => 'in_transit',
        ]);

        return $ship->fresh();
    }

    /**
     * 查询退货轨迹（复用 CnTracking）。
     */
    public function track(ReturnShipment $ship): array
    {
        if (! $ship->tracking_no || ! $ship->shipper_code) {
            return [];
        }

        $svc = '\Plugin\CnTracking\Services\TrackingService';
        if (class_exists($svc)) {
            return $svc::getInstance()->query($ship->shipper_code, $ship->tracking_no);
        }

        return [];
    }

    public function presentShipment(ReturnShipment $ship): array
    {
        $addr = ReturnAddress::query()->find($ship->return_address_id);
        $data = [
            'id'           => $ship->id,
            'aftersale_id' => $ship->aftersale_id,
            'order_number' => $ship->order_number,
            'shipper_code' => $ship->shipper_code,
            'tracking_no'  => $ship->tracking_no,
            'status'       => $ship->status,
            'address'      => $addr ? [
                'name'    => $addr->name,
                'contact' => $addr->contact,
                'phone'   => $addr->phone,
                'full'    => $addr->fullAddress(),
            ] : null,
        ];

        if ($ship->tracking_no) {
            $data['traces'] = $this->track($ship);
        }

        return $data;
    }
}
