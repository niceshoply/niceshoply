<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Referral\Controllers\Console;

use NiceShoply\Console\Controllers\BaseController;
use Plugin\Referral\Models\ReferralBinding;
use Plugin\Referral\Models\ReferralReward;

class ReferralController extends BaseController
{
    protected string $modelClass = ReferralBinding::class;

    public function index(): mixed
    {
        $bindings = ReferralBinding::query()->orderByDesc('id')->paginate(30);
        $rewards  = ReferralReward::query()->orderByDesc('id')->limit(20)->get();
        $totalBindings = ReferralBinding::query()->count();
        $totalRewards  = ReferralReward::query()->count();

        return nice_view('Referral::console.index', compact('bindings', 'rewards', 'totalBindings', 'totalRewards'));
    }
}
