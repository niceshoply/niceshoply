<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\AbandonedCartRepo;
use NiceShoply\Common\Services\AbandonedCart\AbandonedCartService;

/**
 * 弃购挽回后台：列表与转化统计。
 */
class AbandonedCartController extends BaseController
{
    public function index(Request $request): mixed
    {
        $start = $request->input('start', now()->subDays(30)->toDateString());
        $end   = $request->input('end', now()->toDateString());

        $data = [
            'criteria'       => AbandonedCartRepo::getCriteria(),
            'abandonedCarts' => AbandonedCartRepo::getInstance()->list($request->all()),
            'start'          => $start,
            'end'            => $end,
            'stats'          => AbandonedCartService::getInstance()->getStats($start, $end),
        ];

        return nice_view('console::abandoned_carts.index', $data);
    }
}
