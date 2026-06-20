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
use Illuminate\Support\Str;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\CouponUsage;

/**
 * 优惠券数据访问层。
 *
 * 负责按券码检索、有效性判定的查询、客户用券次数统计与批量生成。
 */
class CouponRepo extends BaseRepo
{
    protected string $model = Coupon::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'code', 'type' => 'input', 'label' => trans('console/coupon.code')],
            ['name' => 'type', 'type' => 'select', 'label' => trans('console/coupon.type'), 'options' => self::getTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * 折扣类型下拉选项。
     *
     * @return array
     */
    public static function getTypeOptions(): array
    {
        return [
            ['code' => 'percent', 'label' => trans('console/coupon.type_percent')],
            ['code' => 'fixed', 'label' => trans('console/coupon.type_fixed')],
            ['code' => 'free_shipping', 'label' => trans('console/coupon.type_free_shipping')],
        ];
    }

    /**
     * 后台列表查询构建。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Coupon::query()->with('promotion');

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', 'like', '%'.$code.'%');
        }

        $type = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.coupon.builder', $builder);
    }

    /**
     * 按券码精确查找（大小写不敏感，统一大写存储）。
     *
     * @param  string  $code
     * @return Coupon|null
     */
    public function findByCode(string $code): ?Coupon
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        return Coupon::query()->where('code', $code)->first();
    }

    /**
     * 统计某客户对某券的已核销次数。
     *
     * @param  int  $couponId
     * @param  int  $customerId
     * @return int
     */
    public function customerUsageCount(int $couponId, int $customerId): int
    {
        if ($customerId <= 0) {
            return 0;
        }

        return CouponUsage::query()
            ->where('coupon_id', $couponId)
            ->where('customer_id', $customerId)
            ->count();
    }

    /**
     * 原子递增已用次数（核销时调用）。
     *
     * @param  int  $couponId
     * @return void
     */
    public function incrementUsed(int $couponId): void
    {
        Coupon::query()->where('id', $couponId)->increment('used_count');
    }

    /**
     * 原子递减已用次数（回滚时调用，下限 0）。
     *
     * @param  int  $couponId
     * @return void
     */
    public function decrementUsed(int $couponId): void
    {
        Coupon::query()
            ->where('id', $couponId)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }

    /**
     * 批量生成券码。
     *
     * @param  array  $attributes  公共属性（type/value/min_amount 等）
     * @param  int  $count  生成数量
     * @param  string  $prefix  券码前缀
     * @return array<int, string> 生成的券码列表
     */
    public function batchGenerate(array $attributes, int $count, string $prefix = ''): array
    {
        $count = max(1, min($count, 10000));
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateUniqueCode($prefix);
            Coupon::query()->create(array_merge($attributes, ['code' => $code]));
            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * 生成全局唯一券码。
     *
     * @param  string  $prefix
     * @return string
     */
    private function generateUniqueCode(string $prefix = ''): string
    {
        $prefix = strtoupper(trim($prefix));

        do {
            $code = $prefix.strtoupper(Str::random(10));
        } while (Coupon::query()->where('code', $code)->exists());

        return $code;
    }
}
