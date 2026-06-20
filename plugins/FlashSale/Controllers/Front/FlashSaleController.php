<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlashSale\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\FlashSale\Services\FlashSaleService;

class FlashSaleController extends BaseController
{
    /**
     * 当前生效的秒杀 SKU 列表（前台展示秒杀价/角标）。
     */
    public function active(): mixed
    {
        $items = FlashSaleService::getInstance()->activeItems();

        $data = [];
        foreach ($items as $skuId => $item) {
            $data[] = [
                'sku_id'           => $skuId,
                'product_id'       => $item->product_id,
                'sale_price'       => $item->sale_price,
                'sale_price_format'=> currency_format($item->sale_price),
                'qty_limit'        => $item->qty_limit,
                'sold'             => $item->sold,
            ];
        }

        return json_success('ok', $data);
    }
}
