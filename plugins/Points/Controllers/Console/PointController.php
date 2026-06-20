<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Points\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Points\Models\PointAccount;
use Plugin\Points\Services\PointService;

class PointController extends BaseController
{
    protected string $modelClass = PointAccount::class;

    public function index(Request $request): mixed
    {
        $accounts = PointAccount::query()
            ->when($request->get('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->orderByDesc('balance')
            ->paginate(20)
            ->withQueryString();

        return nice_view('Points::console.index', compact('accounts'));
    }

    /**
     * 后台手动调整某客户积分。
     */
    public function adjust(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'customer_id' => 'required|integer|min:1',
                'change'      => 'required|integer',
                'remark'      => 'nullable|string|max:191',
            ]);

            $balance = PointService::getInstance()->change(
                (int) $data['customer_id'],
                (int) $data['change'],
                'adjust',
                0,
                $data['remark'] ?? ''
            );

            return json_success(__('Points::common.adjusted'), ['balance' => $balance]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
