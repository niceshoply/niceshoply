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
use NiceShoply\Common\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getTags();
        if ($items) {
            Tag::query()->truncate();
            foreach ($items as $item) {
                Tag::query()->create($item);
            }
        }

        $items = $this->getTagTranslations();
        if ($items) {
            Tag\Translation::query()->truncate();
            foreach ($items as $item) {
                Tag\Translation::query()->create($item);
            }
        }
    }

    private function getTags(): array
    {
        return [
            ['id' => 1, 'slug' => 'hiking', 'position' => 1, 'active' => 1],
            ['id' => 2, 'slug' => 'camping', 'position' => 2, 'active' => 1],
            ['id' => 3, 'slug' => 'ultralight', 'position' => 3, 'active' => 1],
        ];
    }

    private function getTagTranslations(): array
    {
        return [
            ['tag_id' => 1, 'locale' => 'zh-cn', 'name' => '徒步'],
            ['tag_id' => 1, 'locale' => 'en', 'name' => 'Hiking'],
            ['tag_id' => 2, 'locale' => 'zh-cn', 'name' => '露营'],
            ['tag_id' => 2, 'locale' => 'en', 'name' => 'Camping'],
            ['tag_id' => 3, 'locale' => 'zh-cn', 'name' => '轻量化'],
            ['tag_id' => 3, 'locale' => 'en', 'name' => 'Ultralight'],
        ];
    }
}
