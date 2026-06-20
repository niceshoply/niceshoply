<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Promotion;
use Throwable;

/**
 * 促销活动数据访问层。
 *
 * 负责后台列表筛选、有效活动检索（时间/分组/启停），以及含翻译的写入。
 */
class PromotionRepo extends BaseRepo
{
    protected string $model = Promotion::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/promotion.name')],
            ['name' => 'scope', 'type' => 'select', 'label' => trans('console/promotion.scope'), 'options' => self::getScopeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'action_type', 'type' => 'select', 'label' => trans('console/promotion.action_type'), 'options' => self::getActionTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * 作用域下拉选项。
     *
     * @return array
     */
    public static function getScopeOptions(): array
    {
        return [
            ['code' => 'cart', 'label' => trans('console/promotion.scope_cart')],
            ['code' => 'product', 'label' => trans('console/promotion.scope_product')],
        ];
    }

    /**
     * 优惠类型下拉选项。
     *
     * @return array
     */
    public static function getActionTypeOptions(): array
    {
        return [
            ['code' => 'percent', 'label' => trans('console/promotion.action_percent')],
            ['code' => 'fixed', 'label' => trans('console/promotion.action_fixed')],
            ['code' => 'free_shipping', 'label' => trans('console/promotion.action_free_shipping')],
        ];
    }

    /**
     * 条件类型下拉选项。
     *
     * @return array
     */
    public static function getConditionTypeOptions(): array
    {
        return [
            ['code' => 'none', 'label' => trans('console/promotion.condition_none')],
            ['code' => 'min_amount', 'label' => trans('console/promotion.condition_min_amount')],
            ['code' => 'min_qty', 'label' => trans('console/promotion.condition_min_qty')],
            ['code' => 'tiered', 'label' => trans('console/promotion.condition_tiered')],
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
        $builder = Promotion::query()->with(['translation', 'translations']);

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', '%'.$name.'%');
        }

        $scope = $filters['scope'] ?? '';
        if ($scope) {
            $builder->where('scope', $scope);
        }

        $actionType = $filters['action_type'] ?? '';
        if ($actionType) {
            $builder->where('action_type', $actionType);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $builder->orderByDesc('priority')->orderByDesc('id');

        return fire_hook_filter('repo.promotion.builder', $builder);
    }

    /**
     * 检索当前可用于结账的促销活动。
     *
     * 过滤：已启用、在有效时间窗内、客户分组匹配、未达总次数上限。
     * 按优先级降序返回，供 PromotionService 依次评估与互斥处理。
     *
     * @param  int  $customerGroupId
     * @return Collection
     */
    public function getActiveForCheckout(int $customerGroupId = 0): Collection
    {
        $now = Carbon::now();

        $builder = Promotion::query()
            ->with(['translation', 'translations'])
            ->where('active', true)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function (Builder $query) {
                // usage_limit=0 表示不限；否则需未达上限
                $query->where('usage_limit', 0)->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderByDesc('priority')
            ->orderBy('id');

        $promotions = $builder->get();

        // 客户分组过滤（customer_group_ids 为空表示全部客户可用）
        return $promotions->filter(function (Promotion $promotion) use ($customerGroupId) {
            $groupIds = $promotion->customer_group_ids ?? [];
            if (empty($groupIds)) {
                return true;
            }

            return in_array($customerGroupId, array_map('intval', $groupIds), true);
        })->values();
    }

    /**
     * 原子递增促销已用次数（核销时调用）。
     *
     * @param  int  $promotionId
     * @return void
     */
    public function incrementUsed(int $promotionId): void
    {
        Promotion::query()->where('id', $promotionId)->increment('used_count');
    }

    /**
     * 原子递减促销已用次数（回滚时调用，下限 0）。
     *
     * @param  int  $promotionId
     * @return void
     */
    public function decrementUsed(int $promotionId): void
    {
        Promotion::query()
            ->where('id', $promotionId)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }

    /**
     * 创建促销活动（含翻译）。
     *
     * @param  array  $data
     * @return Promotion
     * @throws Throwable
     */
    public function create($data): Promotion
    {
        $promotion = new Promotion;
        $this->createOrUpdate($promotion, $data);

        return $promotion;
    }

    /**
     * 更新促销活动（含翻译）。
     *
     * @param  mixed  $item
     * @param  array  $data
     * @return Promotion
     * @throws Throwable
     */
    public function update($item, $data): Promotion
    {
        if (is_int($item)) {
            $item = Promotion::query()->findOrFail($item);
        }
        $this->createOrUpdate($item, $data);

        return $item;
    }

    /**
     * 写入主表与翻译。
     *
     * @param  Promotion  $promotion
     * @param  array  $data
     * @return void
     * @throws Throwable
     */
    private function createOrUpdate(Promotion $promotion, array $data): void
    {
        DB::beginTransaction();

        try {
            $promotion->fill($this->handleData($data));
            $promotion->saveOrFail();

            $this->syncTranslations($promotion, $data['translations'] ?? []);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 规整主表字段（含 JSON 字段防御）。
     *
     * @param  array  $data
     * @return array
     */
    private function handleData(array $data): array
    {
        return [
            'name'               => $data['name'] ?? '',
            'scope'              => $data['scope'] ?? 'cart',
            'condition_type'     => $data['condition_type'] ?? 'none',
            'conditions'         => $this->normalizeJson($data['conditions'] ?? []),
            'action_type'        => $data['action_type'] ?? 'fixed',
            'actions'            => $this->normalizeJson($data['actions'] ?? []),
            'priority'           => (int) ($data['priority'] ?? 0),
            'exclusive'          => (bool) ($data['exclusive'] ?? false),
            'usage_limit'        => (int) ($data['usage_limit'] ?? 0),
            'per_customer_limit' => (int) ($data['per_customer_limit'] ?? 0),
            'customer_group_ids' => $this->normalizeJson($data['customer_group_ids'] ?? []),
            'starts_at'          => $data['starts_at'] ?? null,
            'ends_at'            => $data['ends_at'] ?? null,
            'active'             => (bool) ($data['active'] ?? true),
        ];
    }

    /**
     * 兼容前端可能传入 JSON 字符串或数组。
     *
     * @param  mixed  $value
     * @return array
     */
    private function normalizeJson(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * 同步多语言文案。
     *
     * @param  Promotion  $promotion
     * @param  array  $translations
     * @return void
     */
    private function syncTranslations(Promotion $promotion, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            if (empty($fields['label'])) {
                continue;
            }
            $promotion->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'label'       => $fields['label'],
                    'description' => $fields['description'] ?? '',
                ]
            );
        }
    }
}
