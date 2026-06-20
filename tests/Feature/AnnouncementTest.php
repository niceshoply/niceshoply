<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Announcement;
use NiceShoply\Common\Repositories\AnnouncementRepo;
use Tests\TestCase;

/**
 * 顶部公告测试（IMP-12）
 */
class AnnouncementTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 创建公告应保存翻译，并能通过 getActiveItems 取回。
     */
    public function test_create_with_translations_and_active_items(): void
    {
        $locale = setting_locale_code();

        $announcement = AnnouncementRepo::getInstance()->create([
            'url'          => 'https://niceshoply.com/sale',
            'sort_order'   => 1,
            'active'       => true,
            'translations' => [
                $locale => ['text' => '全场大促销'],
            ],
        ]);

        $this->assertTrue($announcement->active);
        $this->assertSame('全场大促销', $announcement->translations()->where('locale', $locale)->first()->text);

        $items = Announcement::getActiveItems();
        $texts = array_column($items, 'text');
        $this->assertContains('全场大促销', $texts);
    }

    /**
     * 停用的公告不应出现在激活列表中。
     */
    public function test_inactive_announcement_excluded(): void
    {
        $locale = setting_locale_code();

        AnnouncementRepo::getInstance()->create([
            'active'       => false,
            'translations' => [$locale => ['text' => '隐藏公告内容']],
        ]);

        $texts = array_column(Announcement::getActiveItems(), 'text');
        $this->assertNotContains('隐藏公告内容', $texts);
    }

    /**
     * 删除公告应级联删除其翻译。
     */
    public function test_destroy_removes_translations(): void
    {
        $locale = setting_locale_code();

        $announcement = AnnouncementRepo::getInstance()->create([
            'active'       => true,
            'translations' => [$locale => ['text' => '待删除公告']],
        ]);
        $id = $announcement->id;

        AnnouncementRepo::getInstance()->destroy($announcement);

        $this->assertNull(Announcement::find($id));
        $this->assertSame(0, \NiceShoply\Common\Models\Announcement\Translation::where('announcement_id', $id)->count());
    }
}
