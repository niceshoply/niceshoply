<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\ShippingZone;

/**
 * 配送区域数据访问层。
 */
class ShippingZoneRepo extends BaseRepo
{
    protected string $model = ShippingZone::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/shipping.zone_name')],
        ];
    }

    /**
     * 列表查询构建。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = ShippingZone::query();

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $builder->orderByDesc('priority')->orderByDesc('id');

        return fire_hook_filter('repo.shipping_zone.builder', $builder);
    }

    /**
     * 获取按优先级排序的启用区域（供运费计算匹配）。
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return ShippingZone::query()
            ->where('active', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();
    }
}
