<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\MemberLevel;
use Throwable;

/**
 * 会员等级数据访问层。
 */
class MemberLevelRepo extends BaseRepo
{
    protected string $model = MemberLevel::class;

    /**
     * 后台列表筛选条件。
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/member.name')],
            ['name' => 'threshold_type', 'type' => 'select', 'label' => trans('console/member.threshold_type'), 'options' => self::getThresholdTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * 门槛类型选项。
     *
     * @return array<int, array<string, string>>
     */
    public static function getThresholdTypeOptions(): array
    {
        return [
            ['code' => 'amount', 'label' => trans('console/member.threshold_amount')],
            ['code' => 'points', 'label' => trans('console/member.threshold_points')],
        ];
    }

    /**
     * 后台列表查询。
     *
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = MemberLevel::query()->with(['translation', 'translations']);

        if (! empty($filters['name'])) {
            $builder->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (! empty($filters['threshold_type'])) {
            $builder->where('threshold_type', $filters['threshold_type']);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder->orderByDesc('priority')->orderByDesc('id');
    }

    /**
     * 获取所有启用的等级（按 priority 降序）。
     *
     * @return Collection<int, MemberLevel>
     */
    public function getActiveLevels(): Collection
    {
        return MemberLevel::query()
            ->with(['translation', 'translations'])
            ->where('active', true)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 创建等级（含翻译）。
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function create($data): MemberLevel
    {
        $level = new MemberLevel;
        $this->createOrUpdate($level, $data);

        return $level;
    }

    /**
     * 更新等级（含翻译）。
     *
     * @param  mixed  $item
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function update($item, $data): MemberLevel
    {
        if (is_int($item)) {
            $item = MemberLevel::query()->findOrFail($item);
        }
        $this->createOrUpdate($item, $data);

        return $item;
    }

    /**
     * @param  MemberLevel  $level
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    private function createOrUpdate(MemberLevel $level, array $data): void
    {
        DB::beginTransaction();

        try {
            $level->fill([
                'name'             => $data['name'] ?? '',
                'threshold_type'   => $data['threshold_type'] ?? 'amount',
                'threshold_value'  => (float) ($data['threshold_value'] ?? 0),
                'discount_percent' => (float) ($data['discount_percent'] ?? 0),
                'free_shipping'    => (bool) ($data['free_shipping'] ?? false),
                'priority'         => (int) ($data['priority'] ?? 0),
                'active'           => (bool) ($data['active'] ?? true),
            ]);
            $level->saveOrFail();

            $this->syncTranslations($level, $data['translations'] ?? [], $data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 同步多语言文案。
     *
     * @param  array<string, mixed>  $translations
     * @param  array<string, mixed>  $data
     */
    private function syncTranslations(MemberLevel $level, array $translations, array $data): void
    {
        if (empty($translations) && ! empty($data['label'])) {
            $translations[locale_code()] = [
                'label'       => $data['label'],
                'description' => $data['description'] ?? '',
            ];
        }

        foreach ($translations as $locale => $fields) {
            if (empty($fields['label'])) {
                continue;
            }
            $level->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'label'       => $fields['label'],
                    'description' => $fields['description'] ?? '',
                ]
            );
        }
    }
}
