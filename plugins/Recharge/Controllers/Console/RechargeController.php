<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Recharge\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Recharge\Models\RechargeOrder;
use Plugin\Recharge\Models\RechargePlan;

class RechargeController extends BaseController
{
    protected string $modelClass = RechargePlan::class;

    public function plans(): mixed
    {
        $plans = RechargePlan::query()->orderBy('sort')->orderByDesc('id')->paginate(20);

        return nice_view('Recharge::console.plans', compact('plans'));
    }

    public function storePlan(Request $request): mixed
    {
        try {
            RechargePlan::query()->create($this->validatePlan($request));

            return json_success(__('Recharge::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function updatePlan(Request $request, int $id): mixed
    {
        try {
            RechargePlan::query()->findOrFail($id)->update($this->validatePlan($request));

            return json_success(__('Recharge::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroyPlan(int $id): mixed
    {
        try {
            RechargePlan::query()->findOrFail($id)->delete();

            return json_success(__('Recharge::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function records(): mixed
    {
        $records = RechargeOrder::query()->orderByDesc('id')->paginate(30);

        return nice_view('Recharge::console.records', compact('records'));
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|string|max:128',
            'amount'    => 'required|numeric|min:0.01',
            'bonus'     => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'sort'      => 'nullable|integer|min:0',
        ]);
    }
}
