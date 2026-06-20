<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * 野径户外演示仓库 —— 覆盖多仓库存、调拨、发货等功能
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use NiceShoply\Common\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::query()->truncate();

        $items = [
            [
                'code'          => 'WH-CD-WEST',
                'name'          => '成都西部中心仓',
                'description'   => '西南地区主仓，覆盖川藏线及云贵高原订单',
                'contact_name'  => '张仓管',
                'contact_phone' => '028-88880001',
                'country_id'    => 44,
                'country'       => 'China',
                'state_id'      => 0,
                'state'         => 'Sichuan',
                'city'          => 'Chengdu',
                'address_1'     => '武侯区户外装备产业园 B 座 1F',
                'zipcode'       => '610041',
                'latitude'      => 30.572815,
                'longitude'     => 104.066803,
                'priority'      => 0,
                'is_default'    => true,
                'active'        => true,
            ],
            [
                'code'          => 'WH-SH-EAST',
                'name'          => '上海东部枢纽仓',
                'description'   => '华东及沿海订单发货，48 小时加急专线',
                'contact_name'  => '李主管',
                'contact_phone' => '021-88880002',
                'country_id'    => 44,
                'country'       => 'China',
                'state_id'      => 0,
                'state'         => 'Shanghai',
                'city'          => 'Shanghai',
                'address_1'     => '浦东新区物流大道 168 号',
                'zipcode'       => '200120',
                'latitude'      => 31.230416,
                'longitude'     => 121.473701,
                'priority'      => 1,
                'is_default'    => false,
                'active'        => true,
            ],
            [
                'code'          => 'WH-GZ-SOUTH',
                'name'          => '广州南部备货仓',
                'description'   => '华南及东南亚出口订单备货仓',
                'contact_name'  => '王经理',
                'contact_phone' => '020-88880003',
                'country_id'    => 44,
                'country'       => 'China',
                'state_id'      => 0,
                'state'         => 'Guangdong',
                'city'          => 'Guangzhou',
                'address_1'     => '白云区户外物流园 C 区 3 号库',
                'zipcode'       => '510440',
                'latitude'      => 23.129163,
                'longitude'     => 113.264435,
                'priority'      => 2,
                'is_default'    => false,
                'active'        => true,
            ],
        ];

        foreach ($items as $item) {
            Warehouse::query()->create($item);
        }
    }
}
