<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Distribution\Models\DistributionCommission;
use Plugin\Distribution\Models\Distributor;
use Plugin\Distribution\Services\DistributionService;

class DistributionController extends BaseController
{
    protected string $modelClass = DistributionCommission::class;

    public function commissions(Request $request): mixed
    {
        $commissions = DistributionCommission::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('Distribution::console.commissions', compact('commissions'));
    }

    public function distributors(): mixed
    {
        $distributors = Distributor::query()->orderByDesc('total_commission')->paginate(20);

        return nice_view('Distribution::console.distributors', compact('distributors'));
    }

    public function settle(int $id): mixed
    {
        try {
            DistributionService::getInstance()->settle($id);

            return json_success(__('Distribution::common.settled'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
