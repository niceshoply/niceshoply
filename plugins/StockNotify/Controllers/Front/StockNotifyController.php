<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StockNotify\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\StockNotify\Services\StockNotifyService;

class StockNotifyController extends BaseController
{
    public function index(): mixed
    {
        $list = StockNotifyService::getInstance()->listForCustomer((int) token_customer_id());

        return json_success('ok', $list);
    }

    public function subscribe(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'sku_code'     => 'required|string|max:64',
                'type'         => 'nullable|in:restock,price_drop',
                'target_price' => 'nullable|numeric|min:0',
                'product_id'   => 'nullable|integer|min:0',
            ]);

            StockNotifyService::getInstance()->subscribe(
                (int) token_customer_id(),
                $data['sku_code'],
                $data['type'] ?? 'restock',
                (float) ($data['target_price'] ?? 0),
                (int) ($data['product_id'] ?? 0)
            );

            return json_success(__('StockNotify::common.subscribed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function cancel(int $id): mixed
    {
        try {
            StockNotifyService::getInstance()->cancel((int) token_customer_id(), $id);

            return json_success(__('StockNotify::common.cancelled'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
