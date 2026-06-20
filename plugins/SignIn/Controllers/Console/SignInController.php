<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SignIn\Controllers\Console;

use NiceShoply\Console\Controllers\BaseController;
use Plugin\SignIn\Models\SignInLog;

class SignInController extends BaseController
{
    protected string $modelClass = SignInLog::class;

    public function index(): mixed
    {
        $logs = SignInLog::query()->orderByDesc('id')->paginate(30);

        $todayCount = SignInLog::query()->where('sign_date', now()->toDateString())->count();

        return nice_view('SignIn::console.index', compact('logs', 'todayCount'));
    }
}
