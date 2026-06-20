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
use NiceShoply\Common\Models\Category;
use NiceShoply\Common\Repositories\CategoryRepo;
use Throwable;

class CategorySeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $items = $this->getCategories();
        if ($items) {
            Category::query()->truncate();
            foreach ($items as $item) {
                CategoryRepo::getInstance()->create($item);
            }
        }
    }

    /**
     * 野径户外 —— 露营/徒步/攀岩/骑行/配件 五大品类
     *
     * @return array[]
     */
    private function getCategories(): array
    {
        return [
            [
                'slug'         => 'camping',
                'position'     => 1,
                'active'       => 1,
                'image'        => 'images/demo/icons/4.png',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '露营装备',
                        'summary' => '帐篷、睡袋、炉具——打造你的移动营地',
                        'content' => '<div class="category-description">
                            <h3>专业露营装备</h3>
                            <p>从轻量化单人帐到家庭露营套装，覆盖四季露营场景：</p>
                            <ul>
                                <li><strong>帐篷系列</strong> — 防风防雨，快速搭建</li>
                                <li><strong>睡眠系统</strong> — 羽绒/合成睡袋、防潮垫</li>
                                <li><strong>炊饮装备</strong> — 钛合金炉具、便携滤水器</li>
                            </ul>
                            <p>所有产品均经过野外实测，确保在极端天气下依然可靠。</p>
                        </div>',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Camping',
                        'summary' => 'Tents, sleeping bags, stoves — build your mobile basecamp',
                        'content' => '<div class="category-description">
                            <h3>Professional Camping Gear</h3>
                            <p>From ultralight solo tents to family camping sets for all seasons:</p>
                            <ul>
                                <li><strong>Tents</strong> — Windproof, waterproof, quick setup</li>
                                <li><strong>Sleep Systems</strong> — Down/synthetic bags, sleeping pads</li>
                                <li><strong>Cookware</strong> — Titanium stoves, portable water filters</li>
                            </ul>
                            <p>Every product is field-tested for reliability in extreme conditions.</p>
                        </div>',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'tents',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '帐篷', 'content' => '单人帐、双人帐、家庭帐'],
                            ['locale' => 'en', 'name' => 'Tents', 'content' => 'Solo, duo, and family tents'],
                        ],
                    ],
                    [
                        'slug'         => 'sleep-systems',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '睡眠系统', 'content' => '睡袋、防潮垫、充气枕'],
                            ['locale' => 'en', 'name' => 'Sleep Systems', 'content' => 'Sleeping bags, pads, pillows'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'hiking',
                'position'     => 2,
                'active'       => 1,
                'image'        => 'images/demo/icons/5.png',
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => '徒步登山', 'content' => '背包、登山杖、徒步鞋'],
                    ['locale' => 'en', 'name' => 'Hiking', 'content' => 'Backpacks, trekking poles, hiking boots'],
                ],
                'children' => [
                    [
                        'slug'         => 'backpacks',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '登山背包', 'content' => '30L-80L 多容量选择'],
                            ['locale' => 'en', 'name' => 'Backpacks', 'content' => '30L-80L capacity options'],
                        ],
                    ],
                    [
                        'slug'         => 'hiking-footwear',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '徒步鞋靴', 'content' => '中帮/高帮防水徒步鞋'],
                            ['locale' => 'en', 'name' => 'Hiking Footwear', 'content' => 'Mid/high-cut waterproof boots'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'climbing',
                'position'     => 3,
                'active'       => 1,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => '攀岩探险', 'content' => '绳索、安全带、保护器'],
                    ['locale' => 'en', 'name' => 'Climbing', 'content' => 'Ropes, harnesses, protection'],
                ],
                'children' => [
                    [
                        'slug'         => 'ropes-cords',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '绳索辅具', 'content' => '动力绳、静力绳、扁带'],
                            ['locale' => 'en', 'name' => 'Ropes & Cords', 'content' => 'Dynamic/static ropes, slings'],
                        ],
                    ],
                    [
                        'slug'         => 'harness-protection',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '安全带保护', 'content' => '全身安全带、保护器、快挂'],
                            ['locale' => 'en', 'name' => 'Harness & Protection', 'content' => 'Harnesses, belay devices, quickdraws'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'cycling',
                'position'     => 4,
                'active'       => 1,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => '骑行户外', 'content' => '骑行包、车灯、骑行服'],
                    ['locale' => 'en', 'name' => 'Cycling', 'content' => 'Bike packs, lights, cycling apparel'],
                ],
                'children' => [
                    [
                        'slug'         => 'bike-packs',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '骑行包袋', 'content' => '车架包、尾包、驮包'],
                            ['locale' => 'en', 'name' => 'Bike Packs', 'content' => 'Frame bags, saddle bags, panniers'],
                        ],
                    ],
                    [
                        'slug'         => 'bike-lights',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '骑行照明', 'content' => '前灯、尾灯、头灯'],
                            ['locale' => 'en', 'name' => 'Bike Lights', 'content' => 'Front, rear, and head lights'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'outdoor-accessories',
                'position'     => 5,
                'active'       => 1,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => '户外配件', 'content' => '头灯、水具、工具'],
                    ['locale' => 'en', 'name' => 'Accessories', 'content' => 'Headlamps, hydration, tools'],
                ],
                'children' => [
                    [
                        'slug'         => 'headlamps',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '头灯手电', 'content' => '高流明头灯、手电、营地灯'],
                            ['locale' => 'en', 'name' => 'Headlamps', 'content' => 'High-lumen headlamps and flashlights'],
                        ],
                    ],
                    [
                        'slug'         => 'cookware-tools',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '炊具工具', 'content' => '钛杯、多功能刀、Repair Kit'],
                            ['locale' => 'en', 'name' => 'Cookware & Tools', 'content' => 'Titanium cups, multi-tools, repair kits'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
