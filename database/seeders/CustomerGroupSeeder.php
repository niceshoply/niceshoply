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
use NiceShoply\Common\Models\Customer\Group as CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getCustomerGroups();
        if ($items) {
            CustomerGroup::query()->truncate();
            CustomerGroup\Translation::query()->truncate();
            foreach ($items as $item) {
                $translations  = array_pop($item);
                $customerGroup = CustomerGroup::query()->create($item);
                $customerGroup->translations()->createMany($translations);
            }
        }
    }

    private function getCustomerGroups(): array
    {
        return [
            [
                'level'         => 1,
                'mini_cost'     => 0,
                'discount_rate' => 100,
                'translations'  => [
                    ['locale' => 'en', 'name' => 'Trail Rookie', 'description' => 'New outdoor enthusiast'],
                    ['locale' => 'zh-cn', 'name' => '新手驴友', 'description' => '刚踏上户外之路的探险者'],
                ],
            ],
            [
                'level'         => 2,
                'mini_cost'     => 800,
                'discount_rate' => 92,
                'translations'  => [
                    ['locale' => 'en', 'name' => 'Summit Explorer', 'description' => 'VIP member with 8% discount'],
                    ['locale' => 'zh-cn', 'name' => '登顶探险家', 'description' => 'VIP 会员享 92 折优惠'],
                ],
            ],
            [
                'level'         => 3,
                'mini_cost'     => 3000,
                'discount_rate' => 85,
                'translations'  => [
                    ['locale' => 'en', 'name' => 'WildPath Elite', 'description' => 'Elite member with 15% discount'],
                    ['locale' => 'zh-cn', 'name' => '野径精英', 'description' => '精英会员享 85 折优惠'],
                ],
            ],
        ];
    }
}
