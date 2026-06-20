<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * 富文本 HTML 净化服务
 *
 * 统一对入库的富文本字段（商品描述、文章、页面等）进行 XSS 净化，
 * 剥离 <script>、on* 事件、javascript: 等危险内容，仅保留安全白名单标签与属性。
 *
 * 使用方式：
 * - 富文本字段：HtmlPurifyService::clean($html)
 * - 纯文本字段：HtmlPurifyService::strip($html)
 * - 翻译数组：HtmlPurifyService::purifyTranslation($translationArray)
 */
class HtmlPurifyService
{
    /**
     * 已知含富文本的字段，需要走 HTMLPurifier 白名单净化；
     * 其余字段（title、summary、meta_* 等）按纯文本处理，直接剥离标签。
     */
    private const HTML_FIELDS = ['content', 'description'];

    /**
     * HTMLPurifier 单例实例
     */
    private static ?HTMLPurifier $purifier = null;

    /**
     * 净化单段富文本（富文本模式）。
     * 允许安全 HTML 标签，移除脚本、事件处理器等危险内容。
     */
    public static function clean(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return self::getPurifier()->purify($html);
    }

    /**
     * 剥离全部 HTML 标签 —— 用于应为纯文本的字段。
     */
    public static function strip(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return strip_tags($html);
    }

    /**
     * 净化一份翻译数据数组。
     * - 富文本字段（content、description）→ 走 HTMLPurifier 净化
     * - 其余字段 → 剥离所有标签
     *
     * @param  array  $translation
     * @return array
     */
    public static function purifyTranslation(array $translation): array
    {
        foreach ($translation as $key => $value) {
            if ($key === 'locale' || ! is_string($value)) {
                continue;
            }

            if (in_array($key, self::HTML_FIELDS, true)) {
                $translation[$key] = self::clean($value);
            } else {
                $translation[$key] = self::strip($value);
            }
        }

        return $translation;
    }

    /**
     * 获取或创建 HTMLPurifier 单例。
     */
    private static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        // 编码
        $config->set('Core.Encoding', 'UTF-8');

        // 允许 id 属性
        $config->set('Attr.EnableID', true);

        // 不自动包裹 DOCTYPE/html/body
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.AllowedElements', self::getAllowedElements());
        $config->set('HTML.AllowedAttributes', self::getAllowedAttributes());

        // 自动格式化
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);

        // URI：禁止 javascript: 与 data: 等危险协议
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);

        // 安全 iframe —— 为最大安全性禁用
        $config->set('HTML.SafeIframe', false);
        $config->set('URI.SafeIframeRegexp', '');

        // 缓存：使用序列化缓存提升性能
        try {
            $cachePath = storage_path('framework/htmlpurifier');
        } catch (\Throwable) {
            $cachePath = sys_get_temp_dir().'/niceshoply_htmlpurifier';
        }
        if (! is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Cache.SerializerPermissions', 0755);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }

    /**
     * 允许的 HTML 元素白名单。
     */
    private static function getAllowedElements(): string
    {
        return implode(',', [
            // 结构
            'div', 'p', 'br', 'hr', 'blockquote',
            // 标题
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            // 文本
            'span', 'a', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins',
            'sub', 'sup', 'abbr', 'cite', 'code', 'pre', 'small',
            // 列表
            'ul', 'ol', 'li', 'dl', 'dt', 'dd',
            // 表格
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
            // 媒体
            'img',
        ]);
    }

    /**
     * 允许的 HTML 属性白名单。
     */
    private static function getAllowedAttributes(): string
    {
        return implode(',', [
            // 全局
            '*.class', '*.id', '*.title', '*.lang', '*.dir', '*.style',
            // 链接
            'a.href', 'a.target', 'a.rel', 'a.name',
            // 图片
            'img.src', 'img.alt', 'img.width', 'img.height',
            // 表格
            'table.border', 'table.cellpadding', 'table.cellspacing', 'table.width',
            'th.colspan', 'th.rowspan', 'th.scope', 'th.width',
            'td.colspan', 'td.rowspan', 'td.width',
            // 语义
            'abbr.title',
        ]);
    }
}
