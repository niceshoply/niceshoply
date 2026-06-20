<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Resources\ReviewListItem;
use Throwable;

class ReviewController extends BaseController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];

        $list = ReviewRepo::getInstance()->builder($filters)->paginate();

        return ReviewListItem::collection($list);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        try {
            $data = $request->all();

            $data['customer_id'] = token_customer_id();

            $review = ReviewRepo::getInstance()->create($data);

            return create_json_success(new ReviewListItem($review));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Review  $review
     * @return mixed
     */
    public function destroy(Review $review): mixed
    {
        $review->delete();

        return delete_json_success();
    }
}
