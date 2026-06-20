<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Membership\Models\MembershipLevel;
use Plugin\Membership\Services\MembershipService;

class MembershipController extends BaseController
{
    public function mine(): mixed
    {
        $customerId = (int) token_customer_id();
        $membership = MembershipService::getInstance()->getMembership($customerId);

        $current = $membership?->level;
        $next    = MembershipLevel::query()
            ->where('active', true)
            ->where('min_spent', '>', $membership->total_spent ?? 0)
            ->orderBy('min_spent')
            ->first();

        return json_success('ok', [
            'total_spent'      => (float) ($membership->total_spent ?? 0),
            'level_name'       => $current->name ?? null,
            'discount_percent' => (float) ($current->discount_percent ?? 0),
            'next_level_name'  => $next->name ?? null,
            'next_min_spent'   => (float) ($next->min_spent ?? 0),
        ]);
    }
}
