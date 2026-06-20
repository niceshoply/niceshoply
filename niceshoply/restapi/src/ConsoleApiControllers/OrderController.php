<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Resources\OrderSimple;

class OrderController extends BaseController
{
    /**
     * @param  Order  $order
     * @param  Request  $request
     * @return mixed
     */
    public function updateNote(Order $order, Request $request): mixed
    {
        try {
            $adminNote = $request->get('admin_note');
            $order->update([
                'admin_note' => $adminNote,
            ]);

            return update_json_success(new OrderSimple($order));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
