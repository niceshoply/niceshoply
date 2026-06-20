<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\NotifyCenter\Models\MemberNotification;
use Plugin\NotifyCenter\Services\NotifyService;

class NotificationController extends BaseController
{
    protected string $modelClass = MemberNotification::class;

    public function index(): mixed
    {
        $notifications = MemberNotification::query()->orderByDesc('id')->paginate(20);

        return nice_view('NotifyCenter::console.index', compact('notifications'));
    }

    /**
     * 后台手动发送站内信（指定会员或全员广播）。
     */
    public function send(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'customer_id' => 'nullable|integer|min:0',
                'title'       => 'required|string|max:191',
                'content'     => 'nullable|string',
            ]);

            NotifyService::getInstance()->notify(
                (int) ($data['customer_id'] ?? 0),
                $data['title'],
                $data['content'] ?? '',
                'system'
            );

            return json_success(__('NotifyCenter::common.sent'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            MemberNotification::query()->findOrFail($id)->delete();

            return json_success(__('NotifyCenter::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
