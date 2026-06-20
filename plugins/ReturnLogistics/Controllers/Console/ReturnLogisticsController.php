<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReturnLogistics\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ReturnLogistics\Models\ReturnAddress;
use Plugin\ReturnLogistics\Models\ReturnShipment;
use Plugin\ReturnLogistics\Services\ReturnLogisticsService;

class ReturnLogisticsController extends BaseController
{
    protected string $modelClass = ReturnShipment::class;

    public function index(): mixed
    {
        $addresses = ReturnAddress::query()->orderByDesc('id')->get();
        $shipments = ReturnShipment::query()->orderByDesc('id')->paginate(30);

        return nice_view('ReturnLogistics::console.index', compact('addresses', 'shipments'));
    }

    public function storeAddress(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:64',
                'contact'  => 'nullable|string|max:64',
                'phone'    => 'nullable|string|max:32',
                'province' => 'nullable|string|max:64',
                'city'     => 'nullable|string|max:64',
                'area'     => 'nullable|string|max:64',
                'address'  => 'required|string|max:255',
            ]);
            ReturnAddress::query()->create($data + ['is_active' => true]);

            return json_success(__('ReturnLogistics::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function createShipment(Request $request): mixed
    {
        try {
            $data = $request->validate(['aftersale_id' => 'required|integer']);
            $ship = ReturnLogisticsService::getInstance()->createForAftersale((int) $data['aftersale_id']);

            return json_success(__('ReturnLogistics::common.created'), ['id' => $ship->id]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function markReceived(int $id): mixed
    {
        ReturnShipment::query()->whereKey($id)->update(['status' => 'received']);

        return json_success(__('ReturnLogistics::common.received'));
    }
}
