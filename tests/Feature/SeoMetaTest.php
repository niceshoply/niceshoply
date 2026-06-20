<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Libraries\MetaInfo;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Translation;
use NiceShoply\Common\Services\Seo\JsonLdService;
use Tests\TestCase;

/**
 * SEO Meta / JSON-LD 集成测试。
 */
class SeoMetaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_meta_info_returns_custom_canonical(): void
    {
        $product = Product::query()->create([
            'active'       => true,
            'position'     => 0,
            'sales'        => 0,
            'price'        => 99,
            'origin_price' => 99,
            'brand_id'     => 0,
            'tax_class_id' => 0,
            'weight'       => 0,
            'weight_class' => '',
            'slug'         => 'seo-'.uniqid('', true),
            'spu_code'     => 'SEO-SPU-'.uniqid('', true),
        ]);

        Translation::query()->create([
            'product_id'       => $product->id,
            'locale'           => 'zh-cn',
            'name'             => 'SEO Product',
            'meta_title'       => 'SEO Title',
            'meta_description' => 'SEO Desc',
            'canonical'        => 'https://shop.example.com/product-seo',
        ]);

        $product->load('translations');
        $product->setRelation('translation', $product->translations->first());

        $canonical = MetaInfo::getInstance($product)->getCanonical('https://fallback.example.com');
        $this->assertSame('https://shop.example.com/product-seo', $canonical);
    }

    public function test_json_ld_product_schema_contains_name_and_offer(): void
    {
        $product = Product::query()->create([
            'active'       => true,
            'position'     => 0,
            'sales'        => 0,
            'price'        => 88,
            'origin_price' => 88,
            'brand_id'     => 0,
            'tax_class_id' => 0,
            'weight'       => 0,
            'weight_class' => '',
            'slug'         => 'ld-'.uniqid('', true),
            'spu_code'     => 'LD-SPU-'.uniqid('', true),
        ]);

        Translation::query()->create([
            'product_id'       => $product->id,
            'locale'           => 'zh-cn',
            'name'             => 'JSON-LD Product',
            'meta_title'       => 'JSON-LD Title',
            'meta_description' => 'JSON-LD Desc',
        ]);

        $product->load('translations');
        $product->setRelation('translation', $product->translations->first());

        $schema = JsonLdService::getInstance()->product($product);

        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('JSON-LD Title', $schema['name']);
        $this->assertArrayHasKey('offers', $schema);
        $this->assertSame('Offer', $schema['offers']['@type']);
    }

    public function test_json_ld_render_script_outputs_valid_tag(): void
    {
        $html = JsonLdService::getInstance()->renderScript([
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'NiceShoply',
        ]);

        $this->assertStringContainsString('<script type="application/ld+json">', $html);
        $this->assertStringContainsString('NiceShoply', $html);
    }
}
