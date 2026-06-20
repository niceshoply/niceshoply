<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRecovery\Controllers\Console;

use Exception;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\CartRecovery\Models\CartRecoveryLog;
use Plugin\CartRecovery\Services\CartRecoveryService;

class CartRecoveryController extends BaseController
{
    protected string $modelClass = CartRecoveryLog::class;

    public function index(): mixed
    {
        $logs       = CartRecoveryLog::query()->orderByDesc('id')->paginate(30);
        $totalSent  = CartRecoveryLog::query()->count();

        return nice_view('CartRecovery::console.index', compact('logs', 'totalSent'));
    }

    public function scan(): mixed
    {
        try {
            $count = CartRecoveryService::getInstance()->scanAndNotify();

            return json_success(__('CartRecovery::common.scan_done', ['count' => $count]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
