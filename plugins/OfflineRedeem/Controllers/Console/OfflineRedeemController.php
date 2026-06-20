<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OfflineRedeem\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\OfflineRedeem\Models\RedeemCode;
use Plugin\OfflineRedeem\Services\RedeemService;

class OfflineRedeemController extends BaseController
{
    protected string $modelClass = RedeemCode::class;

    public function index(): mixed
    {
        $codes = RedeemCode::query()->orderByDesc('id')->paginate(40);

        return nice_view('OfflineRedeem::console.index', compact('codes'));
    }

    public function generate(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'title'       => 'required|string|max:191',
                'type'        => 'nullable|string|max:32',
                'customer_id' => 'nullable|integer',
            ]);
            $row = RedeemService::getInstance()->generate(
                $data['title'],
                $data['type'] ?? 'voucher',
                0,
                (int) ($data['customer_id'] ?? 0)
            );

            return json_success(__('OfflineRedeem::common.generated'), ['code' => $row->code]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
