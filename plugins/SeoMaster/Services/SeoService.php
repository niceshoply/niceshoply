<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SeoMaster\Services;

use Throwable;

class SeoService
{
    public static function getInstance(): static
    {
        return new static;
    }

    protected function setting(string $key, $default = ''): string
    {
        return (string) plugin_setting('seo_master', $key, $default);
    }

    protected function siteName(): string
    {
        $name = $this->setting('organization_name');
        if ($name !== '') {
            return $name;
        }

        return (string) (function_exists('system_setting') ? system_setting('site_name', config('app.name', '')) : config('app.name', ''));
    }

    /**
     * 渲染注入到 <head> 底部的 SEO 标签。
     */
    public function renderHead(): string
    {
        if (! (bool) plugin_setting('seo_master', 'enabled', true)) {
            return '';
        }

        $url         = e(request()->fullUrl());
        $siteName    = e($this->siteName());
        $description = e($this->setting('default_description'));
        $keywords    = e($this->setting('default_keywords'));
        $ogImage     = e($this->setting('og_image'));
        $twitterSite = e($this->setting('twitter_site'));

        $tags = [];

        // canonical
        $tags[] = '<link rel="canonical" href="'.$url.'">';

        if ($description !== '') {
            $tags[] = '<meta name="description" content="'.$description.'">';
        }
        if ($keywords !== '') {
            $tags[] = '<meta name="keywords" content="'.$keywords.'">';
        }

        // Open Graph
        $tags[] = '<meta property="og:type" content="website">';
        $tags[] = '<meta property="og:url" content="'.$url.'">';
        $tags[] = '<meta property="og:site_name" content="'.$siteName.'">';
        if ($description !== '') {
            $tags[] = '<meta property="og:description" content="'.$description.'">';
        }
        if ($ogImage !== '') {
            $tags[] = '<meta property="og:image" content="'.$ogImage.'">';
        }

        // Twitter Card
        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        if ($twitterSite !== '') {
            $tags[] = '<meta name="twitter:site" content="'.$twitterSite.'">';
        }

        // JSON-LD（首页输出 Organization + WebSite）
        if (request()->is('/') || request()->is(app()->getLocale())) {
            $ld = [
                '@context' => 'https://schema.org',
                '@type'    => 'Organization',
                'name'     => $this->siteName(),
                'url'      => url('/'),
            ];
            if ($ogImage !== '') {
                $ld['logo'] = $this->setting('og_image');
            }
            $tags[] = '<script type="application/ld+json">'.json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>';
        }

        return "\n".implode("\n", $tags)."\n";
    }

    /**
     * robots.txt 内容。
     */
    public function robots(): string
    {
        $custom = $this->setting('robots_txt');
        if (trim($custom) !== '') {
            return $custom;
        }

        $sitemap = url('/sitemap.xml');

        return "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n";
    }

    /**
     * 生成 sitemap.xml。
     */
    public function sitemapXml(): string
    {
        $urls = [];

        // 首页
        $urls[] = ['loc' => url('/'), 'lastmod' => null, 'priority' => '1.0'];

        $this->collectModelUrls($urls, \NiceShoply\Common\Models\Product::class, '0.8');
        $this->collectModelUrls($urls, \NiceShoply\Common\Models\Category::class, '0.7');
        $this->collectModelUrls($urls, \NiceShoply\Common\Models\Page::class, '0.5');
        $this->collectModelUrls($urls, \NiceShoply\Common\Models\Article::class, '0.6');
        $this->collectModelUrls($urls, \NiceShoply\Common\Models\Brand::class, '0.5');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($u['loc']).'</loc>'."\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>'.e($u['lastmod']).'</lastmod>'."\n";
            }
            $xml .= '    <priority>'.$u['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * 安全地收集某模型的 URL（模型不存在或查询失败时忽略）。
     */
    protected function collectModelUrls(array &$urls, string $modelClass, string $priority): void
    {
        if (! class_exists($modelClass)) {
            return;
        }

        try {
            $modelClass::query()->limit(5000)->get()->each(function ($model) use (&$urls, $priority) {
                try {
                    $loc = $model->url ?? '';
                } catch (Throwable) {
                    $loc = '';
                }
                if ($loc) {
                    $urls[] = [
                        'loc'      => $loc,
                        'lastmod'  => optional($model->updated_at)->toAtomString(),
                        'priority' => $priority,
                    ];
                }
            });
        } catch (Throwable) {
            // 忽略不可用的模型/表
        }
    }
}
