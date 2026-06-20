<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\ReviewAftersale\Models\AftersaleRequest;
use Plugin\ReviewAftersale\Services\AftersaleService;

class AftersaleController extends BaseController
{
    public function index(): mixed
    {
        $customerId = (int) token_customer_id();
        $list = AftersaleRequest::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $list);
    }

    public function show(int $id): mixed
    {
        $customerId = (int) token_customer_id();
        $request = AftersaleRequest::query()
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        return json_success('ok', $request);
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'order_id'      => 'required|integer|min:1',
                'type'          => 'required|in:refund,return,exchange',
                'reason'        => 'required|string|max:191',
                'description'   => 'nullable|string|max:2000',
                'images'        => 'nullable|array|max:9',
                'images.*'      => 'string',
                'refund_amount' => 'nullable|numeric|min:0',
            ]);

            $req = AftersaleService::getInstance()->create(
                (int) token_customer_id(),
                (int) $data['order_id'],
                $data['type'],
                $data['reason'],
                $data['description'] ?? '',
                $data['images'] ?? [],
                (float) ($data['refund_amount'] ?? 0)
            );

            return json_success(__('ReviewAftersale::common.aftersale_submitted'), ['number' => $req->number]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
