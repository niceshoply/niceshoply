<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Services\Review\ReviewService;
use NiceShoply\Console\Requests\ReviewRequest;
use Throwable;

class ReviewController extends BaseController
{
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria'     => ReviewRepo::getCriteria(),
            'reviews'      => ReviewRepo::getInstance()->list($filters),
            'pendingCount' => ReviewService::getInstance()->pendingCount(),
        ];

        return nice_view('console::reviews.index', $data);
    }

    public function create(): mixed
    {
        return $this->form(new Review);
    }

    /**
     * @throws Throwable
     */
    public function store(ReviewRequest $request): RedirectResponse
    {
        try {
            $review = ReviewRepo::getInstance()->create($request->all(), true);

            return redirect(console_route('reviews.index'))
                ->with('instance', $review)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('reviews.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Review $review): mixed
    {
        return $this->form($review);
    }

    public function form($review): mixed
    {
        return nice_view('console::reviews.form', [
            'review' => $review,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        try {
            $data = $request->all();
            if (isset($data['content'])) {
                $data['content'] = ReviewService::getInstance()->sanitizeContent($data['content']);
            }
            ReviewRepo::getInstance()->update($review, $data);

            return redirect(console_route('reviews.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('reviews.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Review $review): mixed
    {
        try {
            ReviewRepo::getInstance()->destroy($review);

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 审核通过。
     */
    public function approve(Review $review): mixed
    {
        ReviewService::getInstance()->approve($review);

        return json_success(console_trans('common.updated_success'));
    }

    /**
     * 审核拒绝。
     */
    public function reject(Review $review): mixed
    {
        ReviewService::getInstance()->reject($review);

        return json_success(console_trans('common.updated_success'));
    }

    /**
     * 商家回复。
     */
    public function reply(Request $request, Review $review): mixed
    {
        $request->validate(['reply' => 'required|string|max:2000']);

        try {
            ReviewService::getInstance()->reply($review, $request->input('reply'));

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 商品好评率统计（Ajax）。
     */
    public function stats(Request $request): mixed
    {
        $productId = (int) $request->input('product_id', 0);
        if ($productId <= 0) {
            return json_fail('product_id required');
        }

        return json_success(ReviewService::getInstance()->getProductStats($productId));
    }
}
