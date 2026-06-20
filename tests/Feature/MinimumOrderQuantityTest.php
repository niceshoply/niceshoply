<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Repositories\ProductRepo;
use NiceShoply\Common\Services\CartService;
use Tests\TestCase;

/**
 * 最低起订量（minimum）测试
 *
 * 覆盖：
 * - ProductRepo 持久化 minimum 字段
 * - 加入购物车数量低于最低起订量时抛出异常
 * - 数量达到最低起订量时正常通过
 */
class MinimumOrderQuantityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ProductRepo 应持久化 minimum，且不小于 1。
     */
    public function test_product_repo_persists_minimum(): void
    {
        $product = ProductRepo::getInstance()->create([
            'type'         => Product::TYPE_NORMAL,
            'slug'         => 'min-test-'.uniqid(),
            'active'       => true,
            'minimum'      => 5,
            'translations' => [
                ['locale' => setting_locale_code(), 'name' => '最低起订量测试商品'],
            ],
            'skus' => [
                ['code' => 'MIN-'.uniqid(), 'price' => 10, 'quantity' => 100, 'is_default' => true],
            ],
        ]);

        $this->assertSame(5, (int) $product->fresh()->minimum);
    }

    /**
     * 数量低于最低起订量应抛出异常。
     */
    public function test_add_cart_below_minimum_throws(): void
    {
        [$product, $sku] = $this->makeProductWithMinimum(5, 100);

        $service = CartService::getInstance(0, 'guest-'.uniqid());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(trans('front/common.minimum_required', ['min' => 5]));

        $service->addCart(['sku_id' => $sku->id, 'quantity' => 2]);
    }

    /**
     * 数量达到最低起订量应正常加入。
     */
    public function test_add_cart_meeting_minimum_passes(): void
    {
        [$product, $sku] = $this->makeProductWithMinimum(3, 100);

        $service = CartService::getInstance(0, 'guest-'.uniqid());

        $result = $service->addCart(['sku_id' => $sku->id, 'quantity' => 3]);

        $this->assertIsArray($result);
    }

    /**
     * 构造一个带最低起订量的商品与默认 SKU。
     *
     * @return array{0: Product, 1: Sku}
     */
    private function makeProductWithMinimum(int $minimum, int $quantity): array
    {
        $code = 'MINSKU-'.uniqid();

        $product = ProductRepo::getInstance()->create([
            'type'         => Product::TYPE_NORMAL,
            'slug'         => 'min-'.uniqid(),
            'active'       => true,
            'minimum'      => $minimum,
            'translations' => [
                ['locale' => setting_locale_code(), 'name' => '最低起订量商品'],
            ],
            'skus' => [
                ['code' => $code, 'price' => 10, 'quantity' => $quantity, 'is_default' => true],
            ],
        ]);

        $sku = Sku::query()->where('code', $code)->firstOrFail();

        return [$product->fresh(), $sku];
    }
}
