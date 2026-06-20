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
use NiceShoply\Common\Models\Catalog;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getCatalogs();
        if ($items) {
            Catalog::query()->truncate();
            foreach ($items as $item) {
                Catalog::query()->create($item);
            }
        }

        $items = $this->getCatalogTranslations();
        if ($items) {
            Catalog\Translation::query()->truncate();
            foreach ($items as $item) {
                Catalog\Translation::query()->create($item);
            }
        }
    }

    private function getCatalogs(): array
    {
        return [
            [
                'id'        => 1,
                'parent_id' => 0,
                'slug'      => 'gear-reviews',
                'position'  => 0,
                'active'    => 1,
            ],
            [
                'id'        => 2,
                'parent_id' => 0,
                'slug'      => 'trail-guides',
                'position'  => 1,
                'active'    => 1,
            ],
        ];
    }

    private function getCatalogTranslations(): array
    {
        return [
            [
                'catalog_id'       => 1,
                'locale'           => 'zh-cn',
                'title'            => '装备评测',
                'summary'          => '真实野外测试，帮你选对装备',
                'meta_title'       => '装备评测 | 野径户外',
                'meta_description' => '帐篷、睡袋、背包等户外装备深度评测',
                'meta_keywords'    => '装备评测,户外装备,帐篷评测',
            ],
            [
                'catalog_id'       => 1,
                'locale'           => 'en',
                'title'            => 'Gear Reviews',
                'summary'          => 'Real field tests to help you choose the right gear',
                'meta_title'       => 'Gear Reviews | WildPath Outdoor',
                'meta_description' => 'In-depth reviews of tents, sleeping bags, backpacks and more',
                'meta_keywords'    => 'gear review,outdoor equipment,tent review',
            ],
            [
                'catalog_id'       => 2,
                'locale'           => 'zh-cn',
                'title'            => '线路攻略',
                'summary'          => '经典徒步线路与露营目的地指南',
                'meta_title'       => '线路攻略 | 野径户外',
                'meta_description' => '徒步线路、露营地点、户外安全指南',
                'meta_keywords'    => '徒步攻略,露营地点,户外线路',
            ],
            [
                'catalog_id'       => 2,
                'locale'           => 'en',
                'title'            => 'Trail Guides',
                'summary'          => 'Classic hiking routes and camping destination guides',
                'meta_title'       => 'Trail Guides | WildPath Outdoor',
                'meta_description' => 'Hiking routes, camping spots, and outdoor safety tips',
                'meta_keywords'    => 'trail guide,camping,hiking routes',
            ],
        ];
    }
}
