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
use NiceShoply\Common\Models\ShippingTemplate;

/**
 * 运费模板数据访问层。
 */
class ShippingTemplateRepo extends BaseRepo
{
    protected string $model = ShippingTemplate::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/shipping.template_name')],
            ['name' => 'calc_type', 'type' => 'select', 'label' => trans('console/shipping.calc_type'), 'options' => self::getCalcTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * 计费方式下拉选项。
     *
     * @return array
     */
    public static function getCalcTypeOptions(): array
    {
        return [
            ['code' => 'flat', 'label' => trans('console/shipping.calc_flat')],
            ['code' => 'by_weight', 'label' => trans('console/shipping.calc_by_weight')],
            ['code' => 'by_qty', 'label' => trans('console/shipping.calc_by_qty')],
            ['code' => 'by_amount', 'label' => trans('console/shipping.calc_by_amount')],
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
        $builder = ShippingTemplate::query()->with('zone');

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        $calcType = $filters['calc_type'] ?? '';
        if ($calcType) {
            $builder->where('calc_type', $calcType);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $builder->orderByDesc('priority')->orderByDesc('id');

        return fire_hook_filter('repo.shipping_template.builder', $builder);
    }

    /**
     * 获取启用模板（可按区域过滤）。
     *
     * @param  array  $zoneIds  允许的区域ID（含 0 表示全区域模板）
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveByZones(array $zoneIds): \Illuminate\Database\Eloquent\Collection
    {
        return ShippingTemplate::query()
            ->where('active', true)
            ->where(function (Builder $query) use ($zoneIds) {
                $query->whereNull('zone_id');
                if (! empty($zoneIds)) {
                    $query->orWhereIn('zone_id', $zoneIds);
                }
            })
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();
    }
}
