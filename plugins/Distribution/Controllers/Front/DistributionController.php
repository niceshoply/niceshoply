<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Distribution\Models\DistributionCommission;
use Plugin\Distribution\Models\Distributor;
use Plugin\Distribution\Services\DistributionService;

class DistributionController extends BaseController
{
    /**
     * 申请成为推广员。
     */
    public function become(Request $request): mixed
    {
        try {
            if (! (bool) plugin_setting('distribution', 'self_apply', true)) {
                throw new Exception(__('Distribution::common.self_apply_disabled'));
            }

            $customerId = (int) token_customer_id();
            $parentCode = (string) $request->get('parent_code', '');

            $distributor = DistributionService::getInstance()->becomeDistributor($customerId, $parentCode);

            return json_success('ok', $this->distributorData($distributor));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 绑定推广员邀请码（一次性）。
     */
    public function bind(Request $request): mixed
    {
        $customerId = (int) token_customer_id();
        $code       = (string) $request->get('code', '');

        $ok = DistributionService::getInstance()->bindReferral($customerId, $code);

        return json_success($ok ? __('Distribution::common.bind_ok') : __('Distribution::common.bind_skip'), ['bound' => $ok]);
    }

    /**
     * 我的推广信息。
     */
    public function mine(): mixed
    {
        $customerId  = (int) token_customer_id();
        $distributor = Distributor::query()->where('customer_id', $customerId)->first();

        if (! $distributor) {
            return json_success('ok', ['is_distributor' => false]);
        }

        return json_success('ok', array_merge(['is_distributor' => true], $this->distributorData($distributor)));
    }

    /**
     * 我的佣金记录。
     */
    public function commissions(): mixed
    {
        $customerId = (int) token_customer_id();
        $list = DistributionCommission::query()
            ->where('distributor_customer_id', $customerId)
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $list);
    }

    private function distributorData(Distributor $distributor): array
    {
        return [
            'code'               => $distributor->code,
            'parent_id'          => $distributor->parent_id,
            'total_commission'   => $distributor->total_commission,
            'settled_commission' => $distributor->settled_commission,
            'pending_commission' => round($distributor->total_commission - $distributor->settled_commission, 2),
        ];
    }
}
