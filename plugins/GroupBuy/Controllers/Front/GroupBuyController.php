<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\GroupBuy\Models\GroupBuyGroup;
use Plugin\GroupBuy\Services\GroupBuyService;

class GroupBuyController extends BaseController
{
    /**
     * 团详情（含成员数、剩余名额、状态）。
     */
    public function group(int $id): mixed
    {
        $group = GroupBuyGroup::query()->with('activity')->findOrFail($id);
        $service = GroupBuyService::getInstance();
        $service->refreshStatus($group);

        return json_success('ok', [
            'group_id'      => $group->id,
            'activity_id'   => $group->activity_id,
            'status'        => $group->status,
            'members_count' => $group->members_count,
            'group_size'    => $group->activity->group_size ?? 0,
            'remaining'     => max(($group->activity->group_size ?? 0) - $group->members_count, 0),
            'expire_at'     => $group->expire_at?->toIso8601String(),
            'group_price'   => $group->activity->group_price ?? 0,
        ]);
    }

    /**
     * 选择开团：写入 checkout.reference，结算时按拼团价抵扣。
     */
    public function open(Request $request): mixed
    {
        try {
            $activityId = (int) $request->get('activity_id');
            $customerId = (int) token_customer_id();

            $service  = GroupBuyService::getInstance();
            $activity = $service->getActiveActivity($activityId);
            if (! $activity) {
                throw new Exception(__('GroupBuy::common.activity_invalid'));
            }

            $this->writeReference($customerId, $activityId, 0);

            return json_success(__('GroupBuy::common.open_ready'), ['activity_id' => $activityId]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 选择参团：校验团可加入后写入 checkout.reference。
     */
    public function join(Request $request): mixed
    {
        try {
            $groupId    = (int) $request->get('group_id');
            $customerId = (int) token_customer_id();

            $group = GroupBuyService::getInstance()->ensureJoinable($groupId, $customerId);

            $this->writeReference($customerId, (int) $group->activity_id, $groupId);

            return json_success(__('GroupBuy::common.join_ready'), [
                'group_id'    => $groupId,
                'activity_id' => $group->activity_id,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function writeReference(int $customerId, int $activityId, int $groupId): void
    {
        $checkout                              = CheckoutService::getInstance($customerId);
        $reference                             = $checkout->getCheckoutData()['reference'] ?? [];
        $reference['group_buy_activity_id']    = $activityId;
        $reference['group_buy_group_id']       = $groupId;
        $checkout->updateValues(['reference' => $reference]);
    }
}
