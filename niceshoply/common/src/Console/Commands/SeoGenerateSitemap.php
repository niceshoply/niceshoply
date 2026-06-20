<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NiceShoply\Front\Services\SitemapService;
use Throwable;

/**
 * 生成静态 sitemap.xml（含 hreflang）。
 */
class SeoGenerateSitemap extends Command
{
    protected $signature = 'seo:generate-sitemap {--path= : 输出路径，默认 public/sitemap.xml}';

    protected $description = '生成前台 sitemap.xml（商品/分类/文章/页面 + hreflang）';

    public function handle(): int
    {
        $output = $this->option('path') ?: public_path('sitemap.xml');

        try {
            SitemapService::getInstance()->writeStaticFile($output);
            $this->info('Sitemap 已生成：'.$output);

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('Sitemap 生成失败：'.$e->getMessage());
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
