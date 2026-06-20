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
use NiceShoply\Common\Models\Option;
use NiceShoply\Common\Models\OptionValue;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        OptionValue::query()->truncate();
        Option::query()->truncate();

        $options = $this->getOptions();

        foreach ($options as $index => $opt) {
            $option = Option::query()->create([
                'name'        => $opt['name'],
                'description' => $opt['description'],
                'type'        => $opt['type'],
                'position'    => $index,
                'active'      => true,
                'required'    => $opt['required'] ?? false,
            ]);

            $position = 0;
            foreach ($opt['values'] as $value) {
                OptionValue::query()->create([
                    'option_id' => $option->id,
                    'name'      => $value['name'],
                    'image'     => $value['image'] ?? '',
                    'position'  => $position++,
                    'active'    => true,
                ]);
            }
        }
    }

    /**
     * 户外装备专属商品选项
     */
    private function getOptions(): array
    {
        return [
            [
                'name' => [
                    'zh-cn' => '专业搭建服务',
                    'en'    => 'Setup Service',
                ],
                'description' => [
                    'zh-cn' => '帐篷/大型装备可选上门搭建或视频指导',
                    'en'    => 'On-site setup or video guidance for tents and large gear',
                ],
                'type'     => 'radio',
                'required' => true,
                'values'   => [
                    ['name' => ['zh-cn' => '自行搭建', 'en' => 'Self Setup']],
                    ['name' => ['zh-cn' => '上门搭建服务', 'en' => 'On-site Setup']],
                ],
            ],
            [
                'name' => [
                    'zh-cn' => '附加配件',
                    'en'    => 'Extra Accessories',
                ],
                'description' => [
                    'zh-cn' => '地布、修补包等实用配件',
                    'en'    => 'Groundsheet, repair kit and other practical add-ons',
                ],
                'type'     => 'checkbox',
                'required' => false,
                'values'   => [
                    [
                        'name'  => ['zh-cn' => '防潮地布', 'en' => 'Groundsheet'],
                        'image' => 'images/demo/product/4.png',
                    ],
                    [
                        'name'  => ['zh-cn' => '应急修补包', 'en' => 'Repair Kit'],
                        'image' => 'images/demo/product/8.png',
                    ],
                ],
            ],
            [
                'name' => [
                    'zh-cn' => '加急配送',
                    'en'    => 'Expedited Shipping',
                ],
                'description' => [
                    'zh-cn' => '出发前急需装备？可选 48 小时加急',
                    'en'    => 'Need gear before your trip? Choose 48-hour express',
                ],
                'type'     => 'radio',
                'required' => false,
                'values'   => [
                    ['name' => ['zh-cn' => '标准配送', 'en' => 'Standard Shipping']],
                    ['name' => ['zh-cn' => '48 小时加急', 'en' => '48-Hour Express']],
                ],
            ],
        ];
    }
}
