<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace NiceShoply\Console\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 主题 Demo 安全清库服务（IMP-09）
 *
 * 在导入主题 Demo 前，可选地清空店铺目录（商品/分类/品牌/文章/页面）相关数据，
 * 避免外键冲突导致导入失败。该操作由用户在后台显式勾选并二次确认后触发，
 * 默认不会自动执行；订单/客户等配置类数据不在清理范围内。
 *
 * 在 MySQL/MariaDB 上禁用外键检查，按依赖顺序清空，保证安全。
 */
class ThemeDemoCatalogResetService extends BaseService
{
    /**
     * 默认清理的店铺目录相关表（按外键依赖顺序）。
     *
     * @var string[]
     */
    protected array $defaultTables = [
        'order_return_histories',
        'order_returns',
        'order_option_values',
        'order_fees',
        'order_shipments',
        'order_payments',
        'order_histories',
        'order_items',
        'orders',
        'cart_option_values',
        'cart_items',
        'article_products',
        'customer_favorites',
        'reviews',
        'product_bundles',
        'product_option_values',
        'product_options',
        'product_attributes',
        'product_relations',
        'product_images',
        'product_videos',
        'product_translations',
        'product_skus',
        'product_categories',
        'products',
        'category_translations',
        'category_paths',
        'categories',
        'brand_translations',
        'brands',
        'article_tags',
        'article_relations',
        'article_translations',
        'articles',
        'catalog_translations',
        'catalogs',
        'page_translations',
        'pages',
    ];

    /**
     * 清空默认的店铺目录数据。
     *
     * @param  array|null  $tables  自定义需清空的表（为空时使用默认表）
     * @return array<string> 实际清空的表
     */
    public function clearDefaultCatalogData(?array $tables = null): array
    {
        $tables  = $tables ?: $this->defaultTables;
        $cleared = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->truncate();
                $cleared[] = $table;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        smart_log('info', '[ThemeDemo] 主题 Demo 导入前已清空店铺目录数据', [
            'tables' => $cleared,
        ]);

        return $cleared;
    }
}
