<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use Tests\TestCase;

/**
 * 商品搜索（Scout）集成测试。
 *
 * 使用 Scout 的 `collection` 引擎（无需外部服务），验证 Searchable 与
 * toSearchableArray 配置可按名称 / SKU 编码检索到商品。生产环境将驱动切换为
 * meilisearch 即可获得同样的检索接口与更强的相关性 / 性能。
 */
class ProductSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('scout.driver', 'collection');
        config()->set('scout.enabled', true);
    }

    private function makeProduct(string $name, ?string $skuCode = null): Product
    {
        $product = Product::query()->create([
            'active'   => true, 'position' => 0, 'sales' => 0,
            'price'    => 50, 'origin_price' => 50,
            'brand_id' => 0, 'tax_class_id' => 0, 'weight' => 0, 'weight_class' => '',
            'slug'     => 'search-'.uniqid('', true),
            'spu_code' => 'SEARCH-SPU-'.uniqid('', true),
        ]);

        $product->translations()->create([
            'product_id' => $product->id,
            'locale'     => 'en',
            'name'       => $name,
            'summary'    => $name.' summary',
        ]);

        Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => $skuCode ?: ('SEARCH-SKU-'.uniqid()),
            'price'        => 50,
            'origin_price' => 50,
            'quantity'     => 10,
            'is_default'   => true,
            'position'     => 0,
        ]);

        return $product;
    }

    public function test_searchable_array_contains_expected_keys(): void
    {
        $product = $this->makeProduct('Zephyr Quantum Widget');

        $array = $product->toSearchableArray();

        $this->assertSame($product->id, $array['id']);
        $this->assertContains('Zephyr Quantum Widget', $array['names']);
        $this->assertArrayHasKey('sku_codes', $array);
        $this->assertArrayHasKey('price', $array);
        $this->assertTrue($array['active']);
    }

    public function test_finds_product_by_translated_name(): void
    {
        $token   = 'Zxqwidget'.strtoupper(substr(uniqid(), -6));
        $product = $this->makeProduct("Premium {$token} Pro");

        $ids = Product::search($token)->get()->pluck('id')->all();

        $this->assertContains($product->id, $ids);
    }

    public function test_finds_product_by_sku_code(): void
    {
        $skuToken = 'SKUZX'.strtoupper(substr(uniqid(), -6));
        $product  = $this->makeProduct('Some Generic Name', $skuToken);

        $ids = Product::search($skuToken)->get()->pluck('id')->all();

        $this->assertContains($product->id, $ids);
    }

    public function test_unrelated_keyword_does_not_match(): void
    {
        $product = $this->makeProduct('Alpha Beta Gamma');

        $ids = Product::search('NoSuchKeyword'.uniqid())->get()->pluck('id')->all();

        $this->assertNotContains($product->id, $ids);
    }
}
