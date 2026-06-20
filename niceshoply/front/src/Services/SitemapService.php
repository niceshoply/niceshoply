<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\PageRepo;
use NiceShoply\Common\Repositories\ProductRepo;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Response;

class SitemapService extends BaseService
{
    private Sitemap $sitemap;

    public function __construct()
    {
        $this->sitemap = Sitemap::create();
    }

    /**
     * Render sitemap.xml
     * @param  $request
     * @return Response
     * @throws Exception
     */
    public function response($request): Response
    {
        $locales = enabled_locale_codes();
        $this->sitemap->add(route('front.home.index'));

        foreach ($locales as $locale) {
            $this->addSpecials($locale);
            $this->addProducts($locale);
            $this->addCategories($locale);
            $this->addArticles($locale);
            $this->addPages($locale);
        }

        return $this->sitemap->toResponse($request);
    }

    /**
     * 生成静态 sitemap.xml 文件（含 hreflang alternate，供定时任务使用）。
     */
    public function writeStaticFile(string $path): void
    {
        $sitemap = Sitemap::create();
        $locales = enabled_locale_codes();
        if ($locales === []) {
            $locales = [front_locale_code()];
        }

        $this->addLocalizedUrls($sitemap, $locales, fn ($locale) => $this->frontRoute($locale, 'home.index'));
        $this->addLocalizedUrls($sitemap, $locales, fn ($locale) => $this->frontRoute($locale, 'products.index'));

        $products = ProductRepo::getInstance()->builder(['active' => true])->limit(5000)->get();
        foreach ($products as $item) {
            $this->addLocalizedUrls($sitemap, $locales, function ($locale) use ($item) {
                if ($item->slug) {
                    return $this->frontRoute($locale, 'products.slug_show', ['slug' => $item->slug]);
                }

                return $this->frontRoute($locale, 'products.show', $item);
            }, $item->updated_at);
        }

        $categories = CategoryRepo::getInstance()->builder(['active' => true])->limit(5000)->get();
        foreach ($categories as $item) {
            $this->addLocalizedUrls($sitemap, $locales, function ($locale) use ($item) {
                if ($item->slug) {
                    return $this->frontRoute($locale, 'categories.slug_show', ['slug' => $item->slug]);
                }

                return $this->frontRoute($locale, 'categories.show', $item);
            }, $item->updated_at);
        }

        $articles = ArticleRepo::getInstance()->builder(['active' => true])->limit(5000)->get();
        foreach ($articles as $item) {
            $this->addLocalizedUrls($sitemap, $locales, function ($locale) use ($item) {
                if ($item->slug) {
                    return $this->frontRoute($locale, 'articles.slug_show', ['slug' => $item->slug]);
                }

                return $this->frontRoute($locale, 'articles.show', $item);
            }, $item->updated_at);
        }

        $pages = PageRepo::getInstance()->builder(['active' => true])->limit(5000)->get();
        foreach ($pages as $item) {
            $this->addLocalizedUrls($sitemap, $locales, function ($locale) use ($item) {
                if ($item->slug) {
                    return $this->frontRoute($locale, 'pages.slug_show', ['slug' => $item->slug]);
                }

                return $this->frontRoute($locale, 'pages.show', $item);
            }, $item->updated_at);
        }

        $sitemap->writeToFile($path);
    }

    /**
     * 为同一资源的多语言 URL 添加 hreflang alternate。
     *
     * @param  array<int, string>  $locales
     */
    private function addLocalizedUrls(Sitemap $sitemap, array $locales, callable $resolver, mixed $lastModified = null): void
    {
        $alternates = [];
        foreach ($locales as $locale) {
            $url = $resolver($locale);
            if ($url) {
                $alternates[$locale] = $url;
            }
        }

        if ($alternates === []) {
            return;
        }

        $tag = Url::create(reset($alternates));
        if ($lastModified) {
            $tag->setLastModificationDate($lastModified);
        }

        foreach ($alternates as $localeCode => $alternateUrl) {
            $tag->addAlternate($alternateUrl, str_replace('_', '-', $localeCode));
        }

        $sitemap->add($tag);
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addSpecials($locale): void
    {
        $this->addUrl($this->frontRoute($locale, 'register.index'));
        $this->addUrl($this->frontRoute($locale, 'login.index'));
        $this->addUrl($this->frontRoute($locale, 'products.index'));
        $this->addUrl($this->frontRoute($locale, 'brands.index'));
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addProducts($locale): void
    {
        $products = ProductRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($products as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'products.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'products.show', $item);
            }
            $this->addUrl($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addCategories($locale): void
    {
        $categories = CategoryRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($categories as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'categories.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'categories.show', $item);
            }
            $this->addUrl($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addArticles($locale): void
    {
        $articles = ArticleRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($articles as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'articles.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'articles.show', $item);
            }
            $this->addUrl($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addPages($locale): void
    {
        $pages = PageRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($pages as $item) {
            $url = $this->frontRoute($locale, 'pages.'.$item->slug);

            $this->addUrl($url);
        }
    }

    /**
     * @param  $locale
     * @param  $name
     * @param  mixed  $parameters
     * @return string
     * @throws Exception
     */
    private function frontRoute($locale, $name, mixed $parameters = []): string
    {
        try {
            if (hide_url_locale()) {
                return route('front.'.$name, $parameters);
            }

            return route($locale.'.front.'.$name, $parameters);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return '';
        }
    }

    /**
     * Add a URL to the sitemap
     * @param  mixed  $url
     * @return void
     */
    private function addUrl(mixed $url): void
    {
        if (empty($url)) {
            return;
        }
        $this->sitemap->add($url);
    }
}
