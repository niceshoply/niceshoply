<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\AbandonedCart;

/**
 * 弃购记录数据访问层。
 */
class AbandonedCartRepo extends BaseRepo
{
    protected string $model = AbandonedCart::class;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'email', 'type' => 'input', 'label' => trans('console/abandoned_cart.email')],
            ['name' => 'converted', 'type' => 'select', 'label' => trans('console/abandoned_cart.converted'), 'options' => [
                ['code' => '1', 'label' => trans('console/common.yes')],
                ['code' => '0', 'label' => trans('console/common.no')],
            ], 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = AbandonedCart::query()->with(['customer', 'convertedOrder']);

        if (! empty($filters['email'])) {
            $builder->where('email', 'like', '%'.$filters['email'].'%');
        }

        if (isset($filters['converted']) && $filters['converted'] !== '') {
            $builder->where('converted', (bool) $filters['converted']);
        }

        if (! empty($filters['customer_id'])) {
            $builder->where('customer_id', (int) $filters['customer_id']);
        }

        return $builder->orderByDesc('id');
    }

    public function findByCartKey(string $cartKey): ?AbandonedCart
    {
        return AbandonedCart::query()->where('cart_key', $cartKey)->first();
    }
}
