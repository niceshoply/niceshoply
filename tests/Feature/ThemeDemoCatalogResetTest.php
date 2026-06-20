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
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Repositories\ProductRepo;
use NiceShoply\Console\Services\ThemeDemoCatalogResetService;
use Tests\TestCase;

/**
 * 主题 Demo 安全清库测试（IMP-09）
 */
class ThemeDemoCatalogResetTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 清库后店铺目录数据应被清空，且返回已清空表名。
     */
    public function test_clear_default_catalog_data(): void
    {
        ProductRepo::getInstance()->create([
            'type'         => Product::TYPE_NORMAL,
            'slug'         => 'reset-test-'.uniqid(),
            'active'       => true,
            'translations' => [
                ['locale' => setting_locale_code(), 'name' => '待清空商品'],
            ],
            'skus' => [
                ['code' => 'RST-'.uniqid(), 'price' => 10, 'quantity' => 5, 'is_default' => true],
            ],
        ]);

        $this->assertGreaterThan(0, Product::query()->count());

        $cleared = ThemeDemoCatalogResetService::getInstance()->clearDefaultCatalogData();

        $this->assertContains('products', $cleared);
        $this->assertSame(0, Product::query()->count());
    }

    /**
     * 仅清空指定的表。
     */
    public function test_clear_only_specified_tables(): void
    {
        $cleared = ThemeDemoCatalogResetService::getInstance()->clearDefaultCatalogData(['catalogs']);

        $this->assertSame(['catalogs'], $cleared);
    }
}
