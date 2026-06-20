<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Services;

use Illuminate\Support\Str;
use Plugin\ReviewAftersale\Models\AftersaleRequest;

class AftersaleService
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'processing', 'completed'];

    public const TYPES = ['refund', 'return', 'exchange'];

    public static function getInstance(): static
    {
        return new static;
    }

    public function create(int $customerId, int $orderId, string $type, string $reason, string $description = '', array $images = [], float $refundAmount = 0): AftersaleRequest
    {
        $type = in_array($type, self::TYPES, true) ? $type : 'refund';

        return AftersaleRequest::query()->create([
            'number'        => $this->generateNumber(),
            'order_id'      => $orderId,
            'customer_id'   => $customerId,
            'type'          => $type,
            'reason'        => $reason,
            'description'   => $description,
            'images'        => array_values($images),
            'refund_amount' => $refundAmount,
            'status'        => 'pending',
        ]);
    }

    protected function generateNumber(): string
    {
        do {
            $number = 'AS'.date('Ymd').strtoupper(Str::random(6));
        } while (AftersaleRequest::query()->where('number', $number)->exists());

        return $number;
    }

    public function changeStatus(int $id, string $status, string $remark = ''): AftersaleRequest
    {
        $request = AftersaleRequest::query()->findOrFail($id);
        if (in_array($status, self::STATUSES, true)) {
            $request->status = $status;
        }
        if ($remark !== '') {
            $request->admin_remark = $remark;
        }
        $request->save();

        return $request;
    }
}
