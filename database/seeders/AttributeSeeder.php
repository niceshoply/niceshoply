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
use NiceShoply\Common\Models\Attribute;
use NiceShoply\Common\Models\Attribute\Group as AttributeGroup;
use NiceShoply\Common\Models\Attribute\Group\Translation as AttributeGroupTranslation;
use NiceShoply\Common\Models\Attribute\Translation as AttributeTranslation;
use NiceShoply\Common\Models\Attribute\Value as AttributeValue;
use NiceShoply\Common\Models\Attribute\Value\Translation as AttributeValueTranslation;
use NiceShoply\Common\Models\Product\Attribute as ProductAttribute;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        AttributeGroup::query()->truncate();
        AttributeGroupTranslation::query()->truncate();
        Attribute::query()->truncate();
        AttributeTranslation::query()->truncate();
        AttributeValue::query()->truncate();
        AttributeValueTranslation::query()->truncate();
        ProductAttribute::query()->truncate();

        $attributeGroupsNumber = 4;
        for ($i = 1; $i <= $attributeGroupsNumber; $i++) {
            AttributeGroup::query()->create(['position' => $i]);
        }

        AttributeGroupTranslation::query()->insert(
            collect($this->getGroupTranslations())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        Attribute::query()->insert(
            collect($this->getAttributes())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        AttributeTranslation::query()->insert(
            collect($this->getAttributeTranslations())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        AttributeValue::query()->insert(
            collect($this->getAttributeValues())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        AttributeValueTranslation::query()->insert(
            collect($this->getAttributeValueTranslations())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        ProductAttribute::query()->insert(
            collect($this->productAttributes())->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );
    }

    private function getGroupTranslations(): array
    {
        return [
            ['attribute_group_id' => 1, 'locale' => 'zh-cn', 'name' => '通用'],
            ['attribute_group_id' => 1, 'locale' => 'en', 'name' => 'General'],
            ['attribute_group_id' => 2, 'locale' => 'zh-cn', 'name' => '帐篷Shelter'],
            ['attribute_group_id' => 2, 'locale' => 'en', 'name' => 'Shelter'],
            ['attribute_group_id' => 3, 'locale' => 'zh-cn', 'name' => '服装Apparel'],
            ['attribute_group_id' => 3, 'locale' => 'en', 'name' => 'Apparel'],
            ['attribute_group_id' => 4, 'locale' => 'zh-cn', 'name' => '装备Equipment'],
            ['attribute_group_id' => 4, 'locale' => 'en', 'name' => 'Equipment'],
        ];
    }

    private function getAttributes(): array
    {
        return [
            ['attribute_group_id' => 2, 'category_id' => 1, 'position' => 0],
            ['attribute_group_id' => 2, 'category_id' => 1, 'position' => 1],
            ['attribute_group_id' => 3, 'category_id' => 1, 'position' => 2],
        ];
    }

    private function getAttributeTranslations(): array
    {
        return [
            ['attribute_id' => 1, 'locale' => 'zh-cn', 'name' => '防水指数'],
            ['attribute_id' => 1, 'locale' => 'en', 'name' => 'Waterproof Rating'],
            ['attribute_id' => 2, 'locale' => 'zh-cn', 'name' => '面料材质'],
            ['attribute_id' => 2, 'locale' => 'en', 'name' => 'Material'],
            ['attribute_id' => 3, 'locale' => 'zh-cn', 'name' => '适用季节'],
            ['attribute_id' => 3, 'locale' => 'en', 'name' => 'Season'],
        ];
    }

    private function getAttributeValues(): array
    {
        return [
            ['attribute_id' => 2],
            ['attribute_id' => 2],
            ['attribute_id' => 1],
            ['attribute_id' => 3],
            ['attribute_id' => 2],
            ['attribute_id' => 2],
            ['attribute_id' => 2],
            ['attribute_id' => 3],
            ['attribute_id' => 3],
            ['attribute_id' => 3],
            ['attribute_id' => 1],
            ['attribute_id' => 1],
        ];
    }

    private function getAttributeValueTranslations(): array
    {
        return [
            ['attribute_value_id' => 1, 'locale' => 'zh-cn', 'name' => '20D 尼龙'],
            ['attribute_value_id' => 1, 'locale' => 'en', 'name' => '20D Nylon'],
            ['attribute_value_id' => 2, 'locale' => 'zh-cn', 'name' => 'Gore-Tex'],
            ['attribute_value_id' => 2, 'locale' => 'en', 'name' => 'Gore-Tex'],
            ['attribute_value_id' => 3, 'locale' => 'zh-cn', 'name' => 'PU5000'],
            ['attribute_value_id' => 3, 'locale' => 'en', 'name' => 'PU5000'],
            ['attribute_value_id' => 4, 'locale' => 'zh-cn', 'name' => '四季'],
            ['attribute_value_id' => 4, 'locale' => 'en', 'name' => '4-Season'],
            ['attribute_value_id' => 5, 'locale' => 'zh-cn', 'name' => '800 蓬羽绒'],
            ['attribute_value_id' => 5, 'locale' => 'en', 'name' => '800-Fill Down'],
            ['attribute_value_id' => 6, 'locale' => 'zh-cn', 'name' => '纯钛'],
            ['attribute_value_id' => 6, 'locale' => 'en', 'name' => 'Pure Titanium'],
            ['attribute_value_id' => 7, 'locale' => 'zh-cn', 'name' => '碳纤维'],
            ['attribute_value_id' => 7, 'locale' => 'en', 'name' => 'Carbon Fiber'],
            ['attribute_value_id' => 8, 'locale' => 'zh-cn', 'name' => '夏季'],
            ['attribute_value_id' => 8, 'locale' => 'en', 'name' => 'Summer'],
            ['attribute_value_id' => 9, 'locale' => 'zh-cn', 'name' => '冬季'],
            ['attribute_value_id' => 9, 'locale' => 'en', 'name' => 'Winter'],
            ['attribute_value_id' => 10, 'locale' => 'zh-cn', 'name' => '三季'],
            ['attribute_value_id' => 10, 'locale' => 'en', 'name' => '3-Season'],
            ['attribute_value_id' => 11, 'locale' => 'zh-cn', 'name' => 'PU3000'],
            ['attribute_value_id' => 11, 'locale' => 'en', 'name' => 'PU3000'],
            ['attribute_value_id' => 12, 'locale' => 'zh-cn', 'name' => 'IPX6'],
            ['attribute_value_id' => 12, 'locale' => 'en', 'name' => 'IPX6'],
        ];
    }

    private function productAttributes(): array
    {
        return [
            ['product_id' => 1, 'attribute_id' => 1, 'attribute_value_id' => 3],
            ['product_id' => 1, 'attribute_id' => 2, 'attribute_value_id' => 1],
            ['product_id' => 1, 'attribute_id' => 3, 'attribute_value_id' => 4],
            ['product_id' => 2, 'attribute_id' => 2, 'attribute_value_id' => 7],
            ['product_id' => 2, 'attribute_id' => 3, 'attribute_value_id' => 10],
            ['product_id' => 3, 'attribute_id' => 2, 'attribute_value_id' => 5],
            ['product_id' => 3, 'attribute_id' => 3, 'attribute_value_id' => 9],
            ['product_id' => 4, 'attribute_id' => 2, 'attribute_value_id' => 7],
            ['product_id' => 4, 'attribute_id' => 3, 'attribute_value_id' => 10],
            ['product_id' => 5, 'attribute_id' => 1, 'attribute_value_id' => 3],
            ['product_id' => 5, 'attribute_id' => 2, 'attribute_value_id' => 2],
            ['product_id' => 5, 'attribute_id' => 3, 'attribute_value_id' => 10],
            ['product_id' => 6, 'attribute_id' => 2, 'attribute_value_id' => 2],
            ['product_id' => 6, 'attribute_id' => 3, 'attribute_value_id' => 10],
            ['product_id' => 7, 'attribute_id' => 2, 'attribute_value_id' => 5],
            ['product_id' => 7, 'attribute_id' => 3, 'attribute_value_id' => 9],
            ['product_id' => 8, 'attribute_id' => 2, 'attribute_value_id' => 6],
            ['product_id' => 8, 'attribute_id' => 3, 'attribute_value_id' => 8],
            ['product_id' => 9, 'attribute_id' => 1, 'attribute_value_id' => 12],
            ['product_id' => 9, 'attribute_id' => 3, 'attribute_value_id' => 10],
        ];
    }
}
