<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StockNotify\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\StockNotify\Models\StockNotification;
use Plugin\StockNotify\Services\StockNotifyService;

class StockNotifyController extends BaseController
{
    protected string $modelClass = StockNotification::class;

    public function index(Request $request): mixed
    {
        $subscriptions = StockNotification::query()
            ->when($request->get('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $pendingCount = StockNotification::query()->where('status', 'pending')->count();

        return nice_view('StockNotify::console.index', compact('subscriptions', 'pendingCount'));
    }

    public function scan(): mixed
    {
        try {
            $sent = StockNotifyService::getInstance()->scanAndNotify();

            return json_success(__('StockNotify::common.scan_done', ['count' => $sent]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
