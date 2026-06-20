<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Unit;

use NiceShoply\Common\Services\HtmlPurifyService;
use PHPUnit\Framework\TestCase;

/**
 * HtmlPurifyService 富文本净化单元测试
 *
 * 验证：
 * - <script> 等危险标签被剥离
 * - on* 事件处理器被移除
 * - javascript: 协议链接被移除
 * - 安全标签（p、strong、a 等）被保留
 * - 纯文本字段被完全剥离标签
 */
class HtmlPurifyServiceTest extends TestCase
{
    /**
     * 含 <script> 的 HTML 应被剥离脚本内容。
     */
    public function test_script_tag_is_removed(): void
    {
        $dirty = '<p>正常内容</p><script>alert("xss")</script>';
        $clean = HtmlPurifyService::clean($dirty);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert(', $clean);
        $this->assertStringContainsString('正常内容', $clean);
    }

    /**
     * 内联事件处理器（onclick 等）应被移除。
     */
    public function test_inline_event_handler_is_removed(): void
    {
        $dirty = '<a href="https://niceshoply.com" onclick="steal()">链接</a>';
        $clean = HtmlPurifyService::clean($dirty);

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringContainsString('href="https://niceshoply.com"', $clean);
    }

    /**
     * javascript: 协议链接应被移除。
     */
    public function test_javascript_scheme_is_removed(): void
    {
        $dirty = '<a href="javascript:alert(1)">点我</a>';
        $clean = HtmlPurifyService::clean($dirty);

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    /**
     * 安全标签应被保留。
     */
    public function test_safe_tags_are_preserved(): void
    {
        $html  = '<p><strong>加粗</strong> 与 <em>斜体</em></p>';
        $clean = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('<strong>', $clean);
        $this->assertStringContainsString('<em>', $clean);
    }

    /**
     * strip 应剥离全部标签，仅保留纯文本。
     */
    public function test_strip_removes_all_tags(): void
    {
        $html = '<h1>标题</h1><script>bad()</script>';

        $this->assertSame('标题bad()', HtmlPurifyService::strip($html));
    }

    /**
     * purifyTranslation：富文本字段净化、其余字段剥离标签、locale 保持不变。
     */
    public function test_purify_translation_handles_fields_differently(): void
    {
        $translation = [
            'locale'      => 'zh-cn',
            'name'        => '<b>名称</b><script>x()</script>',
            'content'     => '<p>正文</p><script>y()</script>',
            'description' => '<div onclick="z()">描述</div>',
        ];

        $result = HtmlPurifyService::purifyTranslation($translation);

        // locale 不变
        $this->assertSame('zh-cn', $result['locale']);
        // name 为纯文本字段：标签被剥离
        $this->assertStringNotContainsString('<b>', $result['name']);
        $this->assertStringNotContainsString('<script', $result['name']);
        // content 为富文本字段：保留 <p>，移除 <script>
        $this->assertStringContainsString('<p>', $result['content']);
        $this->assertStringNotContainsString('<script', $result['content']);
        // description 富文本字段：移除 onclick
        $this->assertStringNotContainsString('onclick', $result['description']);
    }

    /**
     * 空字符串应原样返回，避免无谓的净化开销。
     */
    public function test_empty_string_returns_empty(): void
    {
        $this->assertSame('', HtmlPurifyService::clean(''));
        $this->assertSame('', HtmlPurifyService::strip(''));
    }
}
