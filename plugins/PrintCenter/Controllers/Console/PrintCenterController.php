<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrintCenter\Controllers\Console;

use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\PrintCenter\Services\PrintService;

class PrintCenterController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('PrintCenter::console.index');
    }

    public function print(Request $request, string $type): mixed
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->input('ids', ''))));
        $orders = PrintService::getInstance()->orders($ids);

        return nice_view('PrintCenter::console.print', [
            'type'    => in_array($type, ['picking', 'packing'], true) ? $type : 'packing',
            'orders'  => $orders,
            'shop'    => PrintService::getInstance()->shopName(),
            'address' => PrintService::getInstance()->shopAddress(),
        ]);
    }
}
