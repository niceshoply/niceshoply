<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Seo;

use NiceShoply\Common\Libraries\MetaInfo;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Services\BaseService;

/**
 * JSON-LD 结构化数据生成助手。
 */
class JsonLdService extends BaseService
{
    /**
     * 商品 Product 结构化数据。
     *
     * @return array<string, mixed>
     */
    public function product(Product $product): array
    {
        $meta    = MetaInfo::getInstance($product);
        $price   = (float) ($product->masterSku?->price ?? $product->price ?? 0);
        $inStock = ($product->masterSku?->quantity ?? 0) > 0;

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $meta->getTitle(),
            'description' => $meta->getDescription(),
            'sku'         => (string) ($product->masterSku?->code ?? $product->spu_code ?? ''),
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => number_format($price, currency_decimal_place(), '.', ''),
                'priceCurrency' => current_currency_code(),
                'availability'  => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $this->safeProductUrl($product),
            ],
        ];

        $image = $meta->getOgImage();
        if ($image !== '') {
            $schema['image'] = $image;
        }

        return $schema;
    }

    /**
     * 面包屑 BreadcrumbList 结构化数据。
     *
     * @param  array<int, array{name: string, url?: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumb(array $items): array
    {
        $elements = [];
        $position = 1;

        foreach ($items as $item) {
            $entry = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => (string) ($item['name'] ?? ''),
            ];
            if (! empty($item['url'])) {
                $entry['item'] = (string) $item['url'];
            }
            $elements[] = $entry;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * 站点 Organization 结构化数据。
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => (string) system_setting_locale('meta_title', config('app.name', 'NiceShoply')),
            'url'      => function_exists('front_route') ? front_route('home.index') : url('/'),
        ];

        $logo = system_setting('front_logo', '');
        if ($logo !== '') {
            $schema['logo'] = image_origin($logo);
        }

        return $schema;
    }

    /**
     * 将结构化数据渲染为 script 标签 HTML。
     *
     * @param  array<string, mixed>|array<int, array<string, mixed>>  $data
     */
    public function renderScript(array $data): string
    {
        $payload = array_is_list($data) ? $data : [$data];
        $json    = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return '<script type="application/ld+json">'.$json.'</script>';
    }

    /**
     * 合并多个 schema 并渲染。
     *
     * @param  array<int, array<string, mixed>>  $schemas
     */
    public function renderMultiple(array $schemas): string
    {
        return $this->renderScript($schemas);
    }

    private function safeProductUrl(Product $product): string
    {
        try {
            if ($product->slug !== '') {
                return front_route('products.show', ['slug' => $product->slug]);
            }
        } catch (\Throwable) {
            // 测试环境可能未注册前台路由
        }

        return url('/');
    }
}
