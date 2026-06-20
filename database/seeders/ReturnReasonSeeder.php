<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use NiceShoply\Common\Models\ReturnReason;

class ReturnReasonSeeder extends Seeder
{
    public function run(): void
    {
        ReturnReason::query()->truncate();

        $reasons = [
            ['name' => '装备存在质量问题', 'description' => '拉链损坏、面料破损、配件缺失等', 'sort_order' => 1],
            ['name' => '尺码/规格不合适', 'description' => '帐篷容量、背包容量或鞋码不符', 'sort_order' => 2],
            ['name' => '与描述不符', 'description' => '防水指数、重量等参数与页面描述不一致', 'sort_order' => 3],
            ['name' => '出发前取消订单', 'description' => '行程变更，不再需要该装备', 'sort_order' => 4],
            ['name' => '收到商品损坏', 'description' => '运输过程中造成的外包装或产品损坏', 'sort_order' => 5],
        ];

        foreach ($reasons as $reason) {
            ReturnReason::query()->create(array_merge($reason, ['active' => true]));
        }
    }
}
