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
use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Visit\Visit;

/**
 * 访问记录数据访问层
 *
 * 提供后台访问明细列表的查询构建、筛选条件定义与设备/地域选项。
 */
class VisitRepo extends BaseRepo
{
    /**
     * 显式指定模型（BaseRepo 默认按类名推断会得到错误的命名空间）。
     */
    protected string $model = Visit::class;

    /**
     * 后台访问明细列表的筛选条件配置。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'device_type', 'type' => 'select', 'label' => trans('console/visit.device_type'), 'options' => self::getDeviceOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'country_code', 'type' => 'select', 'label' => trans('console/visit.country'), 'options' => self::getCountryOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'browser', 'type' => 'input', 'label' => trans('console/visit.browser')],
            ['name' => 'os', 'type' => 'input', 'label' => trans('console/visit.os')],
            ['name' => 'customer_email', 'type' => 'input', 'label' => trans('console/visit.customer_email')],
            ['name' => 'ip_address', 'type' => 'input', 'label' => trans('console/visit.ip_address')],
            ['name' => 'first_visited_at', 'type' => 'date_range', 'label' => trans('console/visit.visited_at')],
        ];
    }

    /**
     * 设备类型下拉选项。
     *
     * @return array
     */
    public static function getDeviceOptions(): array
    {
        return [
            ['code' => 'desktop', 'label' => trans('console/visit.device_desktop')],
            ['code' => 'mobile', 'label' => trans('console/visit.device_mobile')],
            ['code' => 'tablet', 'label' => trans('console/visit.device_tablet')],
        ];
    }

    /**
     * 地域（国家）下拉选项——来自已有访问记录的去重国家列表。
     *
     * @return array
     */
    public static function getCountryOptions(): array
    {
        return Visit::query()
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->select('country_code', 'country_name')
            ->distinct()
            ->orderBy('country_code')
            ->get()
            ->map(fn ($item) => [
                'code'  => $item->country_code,
                'label' => $item->country_name ?: $item->country_code,
            ])
            ->all();
    }

    /**
     * 构建访问明细查询。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Visit::query()->with('customer');

        $deviceType = $filters['device_type'] ?? '';
        if ($deviceType) {
            $builder->where('device_type', $deviceType);
        }

        $countryCode = $filters['country_code'] ?? '';
        if ($countryCode) {
            $builder->where('country_code', $countryCode);
        }

        $browser = $filters['browser'] ?? '';
        if ($browser) {
            $builder->where('browser', 'like', '%'.$browser.'%');
        }

        $os = $filters['os'] ?? '';
        if ($os) {
            $builder->where('os', 'like', '%'.$os.'%');
        }

        $ipAddress = $filters['ip_address'] ?? '';
        if ($ipAddress) {
            $builder->where('ip_address', 'like', '%'.$ipAddress.'%');
        }

        $customerId = $filters['customer_id'] ?? 0;
        if ($customerId) {
            $builder->where('customer_id', (int) $customerId);
        }

        $customerEmail = $filters['customer_email'] ?? '';
        if ($customerEmail) {
            $builder->whereHas('customer', function ($query) use ($customerEmail) {
                $query->where('email', 'like', '%'.$customerEmail.'%');
            });
        }

        $visitedStart = $filters['first_visited_at_start'] ?? '';
        if ($visitedStart) {
            $builder->where('first_visited_at', '>=', Carbon::parse($visitedStart)->startOfDay());
        }

        $visitedEnd = $filters['first_visited_at_end'] ?? '';
        if ($visitedEnd) {
            $builder->where('first_visited_at', '<=', Carbon::parse($visitedEnd)->endOfDay());
        }

        $builder->orderByDesc('last_visited_at');

        return fire_hook_filter('repo.visit.builder', $builder);
    }

    /**
     * 列表查询：覆盖 BaseRepo 默认 orderByDesc('id')，按最近访问时间排序。
     *
     * @param  array  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->builder($filters)->paginate();
    }
}
