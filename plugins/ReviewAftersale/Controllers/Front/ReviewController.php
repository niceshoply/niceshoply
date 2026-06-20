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
use Plugin\ReviewAftersale\Services\ReviewService;

class ReviewController extends BaseController
{
    public function index(Request $request): mixed
    {
        $productId = (int) $request->get('product_id');
        $service   = ReviewService::getInstance();

        return json_success('ok', [
            'summary' => $service->summary($productId),
            'list'    => $service->listForProduct($productId),
        ]);
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|integer|min:1',
                'rating'     => 'required|integer|min:1|max:5',
                'content'    => 'nullable|string|max:2000',
                'images'     => 'nullable|array|max:9',
                'images.*'   => 'string',
                'order_id'   => 'nullable|integer|min:0',
            ]);

            $review = ReviewService::getInstance()->submit(
                (int) token_customer_id(),
                (int) $data['product_id'],
                (int) $data['rating'],
                $data['content'] ?? '',
                $data['images'] ?? [],
                (int) ($data['order_id'] ?? 0)
            );

            return json_success(__('ReviewAftersale::common.review_submitted'), ['id' => $review->id, 'status' => $review->status]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
