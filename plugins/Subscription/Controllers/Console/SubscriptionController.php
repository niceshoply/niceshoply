<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Subscription\Controllers\Console;

use Exception;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Subscription\Models\Subscription;
use Plugin\Subscription\Services\SubscriptionService;

class SubscriptionController extends BaseController
{
    protected string $modelClass = Subscription::class;

    public function index(): mixed
    {
        $subscriptions = Subscription::query()->orderByDesc('id')->paginate(20);

        $stats = [
            'active'    => Subscription::query()->where('status', Subscription::STATUS_ACTIVE)->count(),
            'paused'    => Subscription::query()->where('status', Subscription::STATUS_PAUSED)->count(),
            'cancelled' => Subscription::query()->where('status', Subscription::STATUS_CANCELLED)->count(),
            'due'       => Subscription::query()->where('status', Subscription::STATUS_ACTIVE)
                ->whereNotNull('next_run_at')->where('next_run_at', '<=', now())->count(),
        ];

        return nice_view('Subscription::console.index', compact('subscriptions', 'stats'));
    }

    /**
     * 后台一键执行到期订阅。
     */
    public function run(): mixed
    {
        try {
            $stats = SubscriptionService::getInstance()->runDue();

            return json_success(__('Subscription::common.run_done', $stats));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
