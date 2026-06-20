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
use NiceShoply\Common\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getBrands();
        if ($items) {
            Brand::query()->truncate();
            foreach ($items as $item) {
                Brand::query()->create($item);
            }
        }
    }

    /**
     * 野径户外演示品牌 —— 与 InnoShop 的 Adidas/Nike 完全不同
     */
    private function getBrands(): array
    {
        return [
            [
                'name'     => 'WildPath',
                'slug'     => 'wildpath',
                'first'    => 'W',
                'logo'     => 'images/demo/icons/1.png',
                'position' => 0,
                'active'   => true,
            ],
            [
                'name'     => 'SummitGear',
                'slug'     => 'summitgear',
                'first'    => 'S',
                'logo'     => 'images/demo/icons/2.png',
                'position' => 1,
                'active'   => true,
            ],
            [
                'name'     => 'TrailFox',
                'slug'     => 'trailfox',
                'first'    => 'T',
                'logo'     => 'images/demo/icons/3.png',
                'position' => 2,
                'active'   => true,
            ],
        ];
    }
}
