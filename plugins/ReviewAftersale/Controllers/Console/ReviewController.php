<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ReviewAftersale\Models\ProductReview;

class ReviewController extends BaseController
{
    protected string $modelClass = ProductReview::class;

    public function index(Request $request): mixed
    {
        $reviews = ProductReview::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('ReviewAftersale::console.reviews', compact('reviews'));
    }

    public function audit(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate([
                'status' => 'required|in:approved,rejected,pending',
                'reply'  => 'nullable|string|max:500',
            ]);

            $review = ProductReview::query()->findOrFail($id);
            $review->status = $data['status'];
            if (isset($data['reply'])) {
                $review->reply = $data['reply'];
            }
            $review->save();

            return json_success(__('ReviewAftersale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            ProductReview::query()->findOrFail($id)->delete();

            return json_success(__('ReviewAftersale::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
