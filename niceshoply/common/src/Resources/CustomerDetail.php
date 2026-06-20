<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use NiceShoply\Common\Models\Customer\Favorite;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Services\Member\MemberLevelService;
use NiceShoply\Common\Services\Member\PointService;
use NiceShoply\Common\Services\StateMachineService;

class CustomerDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        $customerOrders = Order::query()->where('customer_id', $this->id)->get();
        $pointService   = PointService::getInstance();
        $memberLevel    = MemberLevelService::getInstance()->getCustomerLevel((int) $this->id);

        $data = [
            'id'                  => $this->id,
            'email'               => $this->email,
            'name'                => $this->name,
            'avatar'              => image_resize($this->avatar),
            'locale'              => $this->locale,
            'has_password'        => $this->has_password,
            'balance'             => (float) ($this->balance ?? 0),
            'points_balance'      => $pointService->isEnabled() ? $pointService->getBalance((int) $this->id) : null,
            'member_level'        => $memberLevel ? [
                'id'             => $memberLevel->id,
                'name'           => $memberLevel->name,
                'discount_rate'  => (float) $memberLevel->discount_percent / 100,
            ] : null,
            'favorite_count'      => Favorite::query()->where('customer_id', $this->id)->count(),
            'review_count'        => Review::query()->where('customer_id', $this->id)->count(),
            'unpaid_order_total'  => $customerOrders->where('status', StateMachineService::UNPAID)->count(),
            'paid_order_total'    => $customerOrders->where('status', StateMachineService::PAID)->count(),
            'shipped_order_total' => $customerOrders->where('status', StateMachineService::SHIPPED)->count(),
        ];

        return fire_hook_filter('resource.customer.detail', $data);
    }
}
