<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\OrderReturn;
use NiceShoply\Common\Repositories\Order\ItemRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Repositories\OrderReturnRepo;
use NiceShoply\Common\Resources\OrderReturnHistory;
use NiceShoply\Front\Controllers\BaseController;
use Throwable;

class OrderReturnController extends BaseController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $filters['customer_id'] = token_customer_id();

        $orderReturns = OrderReturnRepo::getInstance()->list($filters);

        return read_json_success($orderReturns);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function orderInfo(Request $request): mixed
    {
        $number = $request->get('order_number');
        if (empty($number)) {
            return read_json_success([]);
        }

        $filters = [
            'number'      => $number,
            'customer_id' => token_customer_id(),
        ];
        $order   = OrderRepo::getInstance()->builder($filters)->firstOrFail();
        $options = ItemRepo::getInstance()->getOptions($order);

        $data = [
            'number'  => $number,
            'order'   => $order,
            'options' => $options,
        ];

        return read_json_success($data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception|Throwable
     */
    public function store(Request $request): mixed
    {
        $data = $request->all();
        try {
            $data['customer_id'] = token_customer_id();
            $orderReturn         = OrderReturnRepo::getInstance()->create($data);

            return create_json_success($orderReturn);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  OrderReturn  $orderReturn
     * @return mixed
     */
    public function show(OrderReturn $orderReturn): mixed
    {
        $data = [
            'order_return' => $orderReturn,
            'histories'    => OrderReturnHistory::collection($orderReturn->histories()->get()),
        ];

        return read_json_success($data);
    }

    /**
     * @param  OrderReturn  $order_return
     * @return mixed
     * @throws Exception
     */
    public function destroy(OrderReturn $order_return): mixed
    {
        $order_return->delete();

        return delete_json_success();
    }
}
