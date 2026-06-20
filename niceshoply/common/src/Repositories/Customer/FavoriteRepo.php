<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use NiceShoply\Common\Models\Customer\Favorite;
use NiceShoply\Common\Repositories\BaseRepo;

class FavoriteRepo extends BaseRepo
{
    protected string $model = Favorite::class;

    /**
     * Get list of favorites.
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $favorites = $this->builder($filters)->paginate();

        $validFavorites = $favorites->getCollection()->filter(function ($favorite) {
            if ($favorite->product === null) {
                $favorite->delete();

                return false;
            }

            return true;
        });

        $favorites->setCollection($validFavorites);

        return $favorites;
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        $data = [
            'customer_id' => $data['customer_id'],
            'product_id'  => $data['product_id'],
        ];

        return Favorite::query()->firstOrCreate($data);
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Favorite::query()->with([
            'customer',
            'product.translation',
        ]);

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $productID = $filters['product_id'] ?? 0;
        if ($productID) {
            $builder->where('product_id', $productID);
        }

        return fire_hook_filter('repo.customer.favorite.builder', $builder);
    }
}
