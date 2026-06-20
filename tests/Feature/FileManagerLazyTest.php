<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use NiceShoply\RestAPI\Services\FileManagerService;
use Tests\TestCase;

/**
 * 文件管理器目录懒加载测试（IMP-14）
 */
class FileManagerLazyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // FileManagerService 默认使用 media 磁盘
        Storage::fake(config('filesystems.default'));
        Storage::fake('media');
    }

    /**
     * 懒加载仅返回直接子目录，并正确标记 has_children。
     */
    public function test_lazy_returns_only_direct_children_with_flag(): void
    {
        $disk = Storage::disk('media');
        $disk->makeDirectory('parent');
        $disk->makeDirectory('parent/child');
        $disk->makeDirectory('parent/child/grandchild');
        $disk->makeDirectory('empty');

        $service = new FileManagerService;

        $root  = $service->getDirectoriesLazy('/');
        $names = array_column($root, 'name');
        $this->assertContains('parent', $names);
        $this->assertContains('empty', $names);

        // parent 含子目录 → has_children = true；empty 无子目录 → false
        foreach ($root as $dir) {
            if ($dir['name'] === 'parent') {
                $this->assertTrue($dir['has_children']);
            }
            if ($dir['name'] === 'empty') {
                $this->assertFalse($dir['has_children']);
            }
        }

        // 仅返回直接子级，不应包含孙级目录
        $this->assertNotContains('grandchild', $names);
        $this->assertNotContains('child', $names);

        // 展开 parent 时再取其子级
        $children   = $service->getDirectoriesLazy('/parent');
        $childNames = array_column($children, 'name');
        $this->assertSame(['child'], $childNames);
        $this->assertTrue($children[0]['has_children']);
    }
}
