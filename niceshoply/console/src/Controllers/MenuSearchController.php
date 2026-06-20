<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NiceShoply\Console\Repositories\MenuSearchRepo;

/**
 * 后台菜单全局搜索控制器（IMP-08）
 */
class MenuSearchController extends BaseController
{
    /**
     * 按关键词返回匹配的菜单项。
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = (string) $request->input('keyword', '');
        $items   = MenuSearchRepo::getInstance()->search($keyword);

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }
}
