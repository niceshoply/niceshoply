<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Ewaybill\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Ewaybill\Models\EWaybill;
use Plugin\Ewaybill\Services\EWaybillService;

class EWaybillController extends BaseController
{
    protected string $modelClass = EWaybill::class;

    public function index(): mixed
    {
        $waybills = EWaybill::query()->orderByDesc('id')->paginate(30);

        return nice_view('Ewaybill::console.index', compact('waybills'));
    }

    public function create(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'order_id'     => 'required|integer',
                'shipper_code' => 'required|string|max:16',
            ]);

            $waybill = EWaybillService::getInstance()->create((int) $data['order_id'], $data['shipper_code']);

            if ($waybill->status !== 'success') {
                return json_fail($waybill->message ?: __('Ewaybill::common.failed'));
            }

            return json_success(__('Ewaybill::common.created', ['code' => $waybill->logistic_code]), $waybill);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
