<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Throwable;

class ReviewController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $filters = [
            'customer_id' => current_customer_id(),
        ];

        $data = [
            'reviews' => ReviewRepo::getInstance()->list($filters),
        ];

        return nice_view('account.reviews_index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        $referrerUrl = $request->header('referer');
        try {
            $productID   = $request->get('product_id');
            $orderItemID = $request->get('order_item_id');
            if ($productID) {
                $product = Product::query()->findOrFail($productID);
            } elseif ($orderItemID) {
                $orderItem = Item::query()->findOrFail($orderItemID);
                $product   = $orderItem->product;
            }

            if (empty($product)) {
                throw new Exception('Invalid product.');
            }

            $data = $request->all();

            $data['customer_id'] = current_customer_id();

            ReviewRepo::getInstance()->create($data);

            return redirect($referrerUrl)
                ->with('success', front_route('common.saved_success'));
        } catch (Exception $e) {
            return redirect($referrerUrl)
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Review  $review
     * @return mixed
     */
    public function destroy(Review $review): mixed
    {
        try {
            if ($review->customer_id !== current_customer_id()) {
                return json_fail('Unauthorized: You can only delete your own reviews', null, 403);
            }

            $review->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
