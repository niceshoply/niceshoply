<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\NotifyCenter\Models\MemberNotification;
use Plugin\NotifyCenter\Services\NotifyService;

class NotificationController extends BaseController
{
    public function index(): mixed
    {
        $customerId = (int) token_customer_id();
        $list = MemberNotification::query()
            ->whereIn('customer_id', [$customerId, 0])
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $list);
    }

    public function unreadCount(): mixed
    {
        $customerId = (int) token_customer_id();

        return json_success('ok', ['unread' => NotifyService::getInstance()->unreadCount($customerId)]);
    }

    public function read(Request $request): mixed
    {
        $customerId = (int) token_customer_id();
        $id         = (int) $request->get('id');

        $ok = NotifyService::getInstance()->markRead($customerId, $id);

        return json_success('ok', ['updated' => $ok]);
    }

    public function readAll(): mixed
    {
        $customerId = (int) token_customer_id();
        $count      = NotifyService::getInstance()->markAllRead($customerId);

        return json_success('ok', ['updated' => $count]);
    }
}
