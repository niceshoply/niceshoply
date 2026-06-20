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
use NiceShoply\Common\Models\Product\Translation;
use Tests\TestCase;

/**
 * 翻译回退测试（IMP-15）
 *
 * 验证 Translatable::fallbackName() 的三级回退：
 * 当前语言 → 系统默认语言 → 任意可用语言。
 */
class TranslationFallbackTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 当前语言缺失翻译时，应回退到任意可用语言，而非返回空串。
     */
    public function test_fallback_to_any_available_locale(): void
    {
        $product = Product::query()->create([
            'type'     => Product::TYPE_NORMAL,
            'brand_id' => 0,
            'slug'     => 'fallback-'.uniqid(),
            'active'   => true,
        ]);

        // 仅创建一个非常见语言的翻译，确保当前/默认语言均无对应记录
        Translation::query()->create([
            'product_id' => $product->id,
            'locale'     => 'xx-test',
            'name'       => '仅有的翻译名称',
        ]);

        $product->load('translations');

        // 当前语言无翻译，应回退到唯一可用翻译
        $this->assertSame('仅有的翻译名称', $product->fallbackName('name'));
    }

    /**
     * 存在系统默认语言翻译时，应优先于其他语言返回默认语言值。
     */
    public function test_fallback_to_default_locale(): void
    {
        $product = Product::query()->create([
            'type'     => Product::TYPE_NORMAL,
            'brand_id' => 0,
            'slug'     => 'fallback2-'.uniqid(),
            'active'   => true,
        ]);

        Translation::query()->create([
            'product_id' => $product->id,
            'locale'     => setting_locale_code(),
            'name'       => '默认语言名称',
        ]);
        Translation::query()->create([
            'product_id' => $product->id,
            'locale'     => 'xx-other',
            'name'       => '其他语言名称',
        ]);

        $product->load('translations');

        $this->assertSame('默认语言名称', $product->fallbackName('name'));
    }
}
