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
use NiceShoply\Common\Models\Refund;

/**
 * 退款单数据访问层。
 */
class RefundRepo extends BaseRepo
{
    protected string $model = Refund::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'number', 'type' => 'input', 'label' => trans('console/refund.number')],
            ['name' => 'order_id', 'type' => 'input', 'label' => trans('console/refund.order_id')],
            ['name' => 'status', 'type' => 'select', 'label' => trans('console/refund.status'), 'options' => self::getStatusOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'method', 'type' => 'select', 'label' => trans('console/refund.method'), 'options' => self::getMethodOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * 状态下拉选项。
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        $result = [];
        foreach (['pending', 'processing', 'succeeded', 'failed', 'cancelled'] as $status) {
            $result[] = ['code' => $status, 'label' => trans('common/refund.status_'.$status)];
        }

        return $result;
    }

    /**
     * 退款方式下拉选项。
     *
     * @return array
     */
    public static function getMethodOptions(): array
    {
        $result = [];
        foreach (Refund::METHODS as $method) {
            $result[] = ['code' => $method, 'label' => trans('console/refund.method_'.$method)];
        }

        return $result;
    }

    /**
     * 后台列表查询构建。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Refund::query();

        $number = $filters['number'] ?? '';
        if ($number) {
            $builder->where('number', 'like', '%'.$number.'%');
        }

        $orderId = $filters['order_id'] ?? '';
        if ($orderId) {
            $builder->where('order_id', (int) $orderId);
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        $method = $filters['method'] ?? '';
        if ($method) {
            $builder->where('method', $method);
        }

        $builder->orderByDesc('id');

        return fire_hook_filter('repo.refund.builder', $builder);
    }

    /**
     * 统计订单已成功退款总额（用于防止超额退款）。
     *
     * @param  int  $orderId
     * @return float
     */
    public function succeededAmount(int $orderId): float
    {
        return (float) Refund::query()
            ->where('order_id', $orderId)
            ->whereIn('status', ['succeeded', 'processing', 'pending'])
            ->sum('amount');
    }
}
