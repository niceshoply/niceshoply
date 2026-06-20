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
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Repositories\ProductRepo;

class ProductSeeder extends Seeder
{
    /**
     * @throws \Exception|\Throwable
     */
    public function run(): void
    {
        $items = $this->getProducts();
        if ($items) {
            Product::query()->truncate();
            Product\Translation::query()->truncate();
            Product\Category::query()->truncate();
            Product\Sku::query()->truncate();
            foreach ($items as $item) {
                ProductRepo::getInstance()->create($item);
            }
        }
    }

    /**
     * 野径户外商品 —— 覆盖多规格 SKU、商品选项、多分类关联等全部商品能力
     */
    private function getProducts(): array
    {
        $allCategories = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];

        return [
            [
                'brand_id' => 1,
                'spu_code' => 'alpine-storm-pro-tent',
                'slug'     => 'alpine-storm-pro-tent',
                'images'   => [
                    'images/demo/product/1.png',
                    'images/demo/product/3.png',
                    'images/demo/product/4.png',
                    'images/demo/product/5.png',
                    'images/demo/product/6.png',
                ],
                'hover_image'  => 'images/demo/product/7.png',
                'active'       => true,
                'weight'       => 2.8,
                'translations' => [
                    [
                        'locale'           => 'zh-cn',
                        'name'             => 'Alpine Storm Pro 四季防暴雨帐篷',
                        'summary'          => '20D 硅油尼龙面料，PU5000 防水涂层，抗 8 级风，15 分钟快速搭建。',
                        'content'          => 'Alpine Storm Pro 是 WildPath 旗舰级四季帐篷，采用双交叉帐杆结构，内帐网纱透气防虫，外帐全覆式防雨。适合高海拔徒步、长线穿越与家庭露营。附赠地钉、风绳、修补包。',
                        'selling_point'    => '1. PU5000 暴雨防护  2. 双交叉帐杆抗风  3. 内外帐分离四季可用  4. 附赠完整配件包',
                        'meta_title'       => 'Alpine Storm Pro 四季帐篷 | WildPath',
                        'meta_description' => '专业四季防暴雨帐篷，PU5000 防水，抗 8 级风，快速搭建，适合高海拔徒步与露营。',
                        'meta_keywords'    => '帐篷,四季帐,防暴雨,WildPath,露营',
                    ],
                    [
                        'locale'           => 'en',
                        'name'             => 'Alpine Storm Pro 4-Season Stormproof Tent',
                        'summary'          => '20D silicone nylon, PU5000 waterproof, withstands Force 8 winds, 15-min setup.',
                        'content'          => 'The Alpine Storm Pro is WildPath\'s flagship 4-season tent with cross-pole architecture, breathable mesh inner, and full-coverage rainfly. Ideal for alpine trekking, thru-hikes, and family camping. Includes stakes, guy lines, and repair kit.',
                        'selling_point'    => '1. PU5000 storm protection  2. Cross-pole wind resistance  3. Double-wall 4-season design  4. Complete accessory kit included',
                        'meta_title'       => 'Alpine Storm Pro Tent | WildPath Outdoor',
                        'meta_description' => 'Professional 4-season stormproof tent with PU5000 waterproofing, Force 8 wind resistance, and quick setup.',
                        'meta_keywords'    => 'tent,4-season,waterproof,WildPath,camping',
                    ],
                ],
                'skus' => [
                    ['code' => 'ASP-FG-2P', 'image' => 'images/demo/product/1.png', 'price' => 189.00, 'origin_price' => 249.00, 'quantity' => 45, 'variants' => [0, 0], 'is_default' => true],
                    ['code' => 'ASP-FG-4P', 'image' => 'images/demo/product/1.png', 'price' => 259.00, 'origin_price' => 329.00, 'quantity' => 38, 'variants' => [0, 1]],
                    ['code' => 'ASP-DT-2P', 'image' => 'images/demo/product/7.png', 'price' => 189.00, 'origin_price' => 249.00, 'quantity' => 52, 'variants' => [1, 0]],
                    ['code' => 'ASP-DT-4P', 'image' => 'images/demo/product/7.png', 'price' => 259.00, 'origin_price' => 329.00, 'quantity' => 41, 'variants' => [1, 1]],
                    ['code' => 'ASP-SG-2P', 'image' => 'images/demo/product/3.png', 'price' => 199.00, 'origin_price' => 259.00, 'quantity' => 30, 'variants' => [2, 0]],
                    ['code' => 'ASP-SG-4P', 'image' => 'images/demo/product/3.png', 'price' => 269.00, 'origin_price' => 339.00, 'quantity' => 28, 'variants' => [2, 1]],
                ],
                'variables' => [
                    [
                        'name'   => ['en' => 'Color', 'zh-cn' => '颜色'],
                        'values' => [
                            ['image' => 'images/demo/product/1.png', 'name' => ['en' => 'Forest Green', 'zh-cn' => '森林绿']],
                            ['image' => 'images/demo/product/7.png', 'name' => ['en' => 'Desert Tan', 'zh-cn' => '沙漠卡其']],
                            ['image' => 'images/demo/product/3.png', 'name' => ['en' => 'Slate Gray', 'zh-cn' => '板岩灰']],
                        ],
                    ],
                    [
                        'name'   => ['en' => 'Capacity', 'zh-cn' => '容量'],
                        'values' => [
                            ['image' => '', 'name' => ['en' => '2-Person', 'zh-cn' => '双人']],
                            ['image' => '', 'name' => ['en' => '4-Person', 'zh-cn' => '四人']],
                        ],
                    ],
                ],
                'product_options' => [
                    [
                        'option_id' => 1,
                        'values'    => [
                            ['option_value_id' => 1, 'price_adjustment' => 0.00,  'stock_quantity' => 200],
                            ['option_value_id' => 2, 'price_adjustment' => 35.00, 'stock_quantity' => 100],
                        ],
                    ],
                    [
                        'option_id' => 2,
                        'values'    => [
                            ['option_value_id' => 3, 'price_adjustment' => 18.00, 'stock_quantity' => 150],
                            ['option_value_id' => 4, 'price_adjustment' => 12.00, 'stock_quantity' => 200],
                        ],
                    ],
                    [
                        'option_id' => 3,
                        'values'    => [
                            ['option_value_id' => 5, 'price_adjustment' => 0.00,  'stock_quantity' => 999],
                            ['option_value_id' => 6, 'price_adjustment' => 45.00, 'stock_quantity' => 80],
                        ],
                    ],
                ],
                'categories' => $allCategories,
            ],
            [
                'brand_id'     => 1,
                'spu_code'     => 'trailmaster-65l-backpack',
                'slug'         => 'trailmaster-65l-backpack',
                'images'       => ['images/demo/product/2.png'],
                'active'       => true,
                'weight'       => 1.85,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'TrailMaster 65L 重装登山背包', 'summary' => '可调节背负系统，65L 大容量，侧袋可放登山杖。'],
                    ['locale' => 'en', 'name' => 'TrailMaster 65L Expedition Backpack', 'summary' => 'Adjustable suspension, 65L capacity, side pockets for trekking poles.'],
                ],
                'skus' => [
                    ['code' => 'TM65L-001', 'image' => 'images/demo/product/2.png', 'price' => 168.00, 'origin_price' => 219.00, 'quantity' => 60],
                ],
                'categories' => [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'spu_code'     => 'arctic-comfort-sleeping-bag',
                'slug'         => 'arctic-comfort-sleeping-bag',
                'images'       => ['images/demo/product/3.png'],
                'active'       => true,
                'weight'       => 1.2,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'Arctic Comfort -10°C 羽绒睡袋', 'summary' => '800 蓬松度白鸭绒，极限舒适温标 -10°C，压缩后仅 28×18cm。'],
                    ['locale' => 'en', 'name' => 'Arctic Comfort -10°C Down Sleeping Bag', 'summary' => '800-fill white duck down, comfort rating -10°C, packs to 28×18cm.'],
                ],
                'skus' => [
                    ['code' => 'ACSB-001', 'image' => 'images/demo/product/3.png', 'price' => 299.00, 'origin_price' => 399.00, 'quantity' => 35],
                ],
                'categories' => [1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'spu_code'     => 'summit-carbon-trekking-poles',
                'slug'         => 'summit-carbon-trekking-poles',
                'images'       => ['images/demo/product/4.png'],
                'active'       => true,
                'weight'       => 0.48,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'Summit 碳纤维折叠登山杖（一对）', 'summary' => '7075 铝合金+碳纤维混合，三段折叠，EVA 长握把。'],
                    ['locale' => 'en', 'name' => 'Summit Carbon Folding Trekking Poles (Pair)', 'summary' => '7075 aluminum + carbon blend, 3-section fold, extended EVA grip.'],
                ],
                'skus' => [
                    ['code' => 'SCTP-001', 'image' => 'images/demo/product/4.png', 'price' => 79.00, 'origin_price' => 99.00, 'quantity' => 120],
                ],
                'categories' => [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'spu_code'     => 'ripstop-rain-jacket',
                'slug'         => 'ripstop-rain-jacket',
                'images'       => ['images/demo/product/5.png'],
                'active'       => true,
                'weight'       => 0.35,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'Ripstop 5000mm 轻量冲锋衣', 'summary' => '全压胶接缝，可收纳至胸袋，仅重 350g。'],
                    ['locale' => 'en', 'name' => 'Ripstop 5000mm Lightweight Rain Jacket', 'summary' => 'Fully taped seams, packs into chest pocket, only 350g.'],
                ],
                'skus' => [
                    ['code' => 'RRJ-001', 'image' => 'images/demo/product/5.png', 'price' => 129.00, 'origin_price' => 169.00, 'quantity' => 85],
                ],
                'categories' => [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 3,
                'spu_code'     => 'trailblaze-hiking-boots',
                'slug'         => 'trailblaze-hiking-boots',
                'images'       => ['images/demo/product/6.png'],
                'active'       => true,
                'weight'       => 0.95,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'TrailBlaze 中帮防水徒步靴', 'summary' => 'Vibram 大底，Gore-Tex 内衬，适合中长途徒步。'],
                    ['locale' => 'en', 'name' => 'TrailBlaze Mid-Cut Waterproof Hiking Boots', 'summary' => 'Vibram outsole, Gore-Tex lining, built for multi-day treks.'],
                ],
                'skus' => [
                    ['code' => 'THB-001', 'image' => 'images/demo/product/6.png', 'price' => 159.00, 'origin_price' => null, 'quantity' => 70],
                ],
                'categories' => [1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'spu_code'     => 'featherlite-down-jacket',
                'slug'         => 'featherlite-down-jacket',
                'images'       => ['images/demo/product/7.png'],
                'active'       => true,
                'weight'       => 0.28,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'FeatherLite 800 蓬轻量化羽绒服', 'summary' => '仅 280g，可压缩至拳头大小，适合高海拔保暖层。'],
                    ['locale' => 'en', 'name' => 'FeatherLite 800-Fill Ultralight Down Jacket', 'summary' => 'Only 280g, packs to fist-size, ideal alpine insulation layer.'],
                ],
                'skus' => [
                    ['code' => 'FLDJ-001', 'image' => 'images/demo/product/7.png', 'price' => 219.00, 'origin_price' => null, 'quantity' => 55],
                ],
                'categories' => [1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 3,
                'spu_code'     => 'campfire-titanium-cookset',
                'slug'         => 'campfire-titanium-cookset',
                'images'       => ['images/demo/product/8.png'],
                'active'       => true,
                'weight'       => 0.22,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'CampFire 钛合金套锅（3 件套）', 'summary' => '纯钛材质，锅+盖+杯，总重 220g，适合 1-2 人。'],
                    ['locale' => 'en', 'name' => 'CampFire Titanium Cookset (3-Piece)', 'summary' => 'Pure titanium pot, lid, and cup — 220g total, serves 1-2.'],
                ],
                'skus' => [
                    ['code' => 'CTC-001', 'image' => 'images/demo/product/8.png', 'price' => 89.00, 'origin_price' => null, 'quantity' => 95],
                ],
                'categories' => [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'spu_code'     => 'nightguide-headlamp',
                'slug'         => 'nightguide-headlamp',
                'images'       => ['images/demo/product/9.png'],
                'active'       => true,
                'weight'       => 0.08,
                'translations' => [
                    ['locale' => 'zh-cn', 'name' => 'NightGuide 1200lm 感应头灯', 'summary' => '1200 流明主灯，红光夜视模式，IPX6 防水，续航 80 小时。'],
                    ['locale' => 'en', 'name' => 'NightGuide 1200lm Sensor Headlamp', 'summary' => '1200-lumen main beam, red night-vision mode, IPX6, 80hr runtime.'],
                ],
                'skus' => [
                    ['code' => 'NGH-001', 'image' => 'images/demo/product/9.png', 'price' => 59.00, 'origin_price' => null, 'quantity' => 150],
                ],
                'categories' => [1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 13, 14, 15],
            ],
        ];
    }
}
