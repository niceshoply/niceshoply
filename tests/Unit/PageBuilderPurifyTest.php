<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Plugin\PageBuilder\Services\DesignService;

/**
 * PageBuilder 模块入库 XSS 净化单元测试
 *
 * 验证 DesignService::handleRequestModules 在保存模块时：
 * - 富文本字段（content）走白名单净化，移除 <script>、保留安全标签；
 * - 纯文本字段（title/subtitle 等）剥离全部标签；
 * - 多语言数组逐语言处理；
 * - 缺失 module_id 时自动补全。
 */
class PageBuilderPurifyTest extends TestCase
{
    /**
     * 富文本模块内容应被净化后再入库。
     */
    public function test_rich_text_content_is_sanitized_on_save(): void
    {
        $modules = [
            'modules' => [
                [
                    'code'      => 'rich-text',
                    'module_id' => 'fixed-id',
                    'view_path' => '',
                    'content'   => [
                        'title'   => ['zh_cn' => '<b>标题</b><script>steal()</script>'],
                        'content' => ['zh_cn' => '<p>正文</p><script>alert(1)</script>'],
                    ],
                ],
            ],
        ];

        $result = DesignService::getInstance()->handleRequestModules($modules);

        $content = $result['modules'][0]['content'];

        // 富文本字段：保留 <p>，移除 <script>
        $this->assertStringContainsString('<p>', $content['content']['zh_cn']);
        $this->assertStringNotContainsString('<script', $content['content']['zh_cn']);
        $this->assertStringNotContainsString('alert(', $content['content']['zh_cn']);

        // 纯文本字段：标签被剥离
        $this->assertStringNotContainsString('<b>', $content['title']['zh_cn']);
        $this->assertStringNotContainsString('<script', $content['title']['zh_cn']);
        $this->assertStringContainsString('标题', $content['title']['zh_cn']);
    }

    /**
     * 内联事件处理器应在嵌套图片描述中被移除。
     */
    public function test_nested_array_fields_are_sanitized(): void
    {
        $modules = [
            'modules' => [
                [
                    'code'      => 'four-image',
                    'module_id' => 'img-module',
                    'view_path' => '',
                    'content'   => [
                        'images' => [
                            [
                                'image'       => 'demo.jpg',
                                'description' => ['zh_cn' => '<div onclick="x()">说明</div>'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = DesignService::getInstance()->handleRequestModules($modules);

        $description = $result['modules'][0]['content']['images'][0]['description']['zh_cn'];

        // description 属纯文本字段：标签（含 onclick）被剥离
        $this->assertStringNotContainsString('onclick', $description);
        $this->assertStringNotContainsString('<div', $description);
        $this->assertStringContainsString('说明', $description);
    }

    /**
     * 缺失 module_id 时应自动补全随机 ID。
     */
    public function test_missing_module_id_is_generated(): void
    {
        $modules = [
            'modules' => [
                [
                    'code'    => 'rich-text',
                    'content' => ['content' => ['zh_cn' => '<p>hi</p>']],
                ],
            ],
        ];

        $result = DesignService::getInstance()->handleRequestModules($modules);

        $this->assertNotEmpty($result['modules'][0]['module_id']);
    }
}
