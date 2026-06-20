<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmartRecommend\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\SmartRecommend\Services\RecommendService;

class RecommendController extends BaseController
{
    /**
     * 解析访客标识：已登录用 c:{id}，否则用前端传的 visitor_id（v:{id}）。
     */
    protected function visitorKey(Request $request): string
    {
        $customerId = token_customer_id();
        if ($customerId) {
            return 'c:'.$customerId;
        }

        $visitor = (string) $request->input('visitor_id', '');
        $visitor = preg_replace('/[^A-Za-z0-9_\-]/', '', $visitor);

        return $visitor !== '' ? 'v:'.substr($visitor, 0, 60) : '';
    }

    /**
     * 记录浏览。
     */
    public function view(Request $request): mixed
    {
        $data = $request->validate(['product_id' => 'required|integer']);
        $key  = $this->visitorKey($request);
        RecommendService::getInstance()->recordView($key, (int) $data['product_id']);

        return json_success('ok');
    }

    public function recentlyViewed(Request $request): mixed
    {
        $exclude = (int) $request->input('exclude_id', 0) ?: null;

        return json_success('ok', RecommendService::getInstance()
            ->recentlyViewed($this->visitorKey($request), $exclude));
    }

    public function forYou(Request $request): mixed
    {
        return json_success('ok', RecommendService::getInstance()->forYou($this->visitorKey($request)));
    }

    public function viewedAlsoViewed(Request $request, int $productId): mixed
    {
        return json_success('ok', RecommendService::getInstance()->viewedAlsoViewed($productId));
    }

    public function boughtTogether(Request $request, int $productId): mixed
    {
        return json_success('ok', RecommendService::getInstance()->boughtTogether($productId));
    }

    public function hot(Request $request): mixed
    {
        return json_success('ok', RecommendService::getInstance()->hot());
    }
}
