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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Announcement;
use Throwable;

/**
 * 公告仓库
 */
class AnnouncementRepo extends BaseRepo
{
    protected string $model = Announcement::class;

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderBy('sort_order')->orderByDesc('id')->paginate();
    }

    /**
     * 构造查询。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Announcement::query()->with(['translation']);

        if (isset($filters['active']) && $filters['active'] !== '') {
            $builder->where('active', (bool) $filters['active']);
        }

        $keyword = $filters['keyword'] ?? ($filters['text'] ?? '');
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('plugin_code', 'like', "%{$keyword}%")
                    ->orWhereHas('translation', function ($q) use ($keyword) {
                        $q->where('text', 'like', "%{$keyword}%");
                    });
            });
        }

        return fire_hook_filter('repo.announcement.builder', $builder);
    }

    /**
     * 创建公告。
     *
     * @param  array  $data
     * @return Announcement
     * @throws Throwable
     */
    public function create($data): Announcement
    {
        return $this->createOrUpdate(new Announcement, $data);
    }

    /**
     * 更新公告。
     *
     * @param  Announcement  $item
     * @param  array  $data
     * @return mixed
     * @throws Throwable
     */
    public function update($item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * 创建或更新。
     *
     * @param  Announcement  $item
     * @param  array  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Announcement $item, $data): mixed
    {
        DB::beginTransaction();

        try {
            $item->fill([
                'plugin_code' => $data['plugin_code'] ?? null,
                'url'         => $data['url'] ?? null,
                'sort_order'  => (int) ($data['sort_order'] ?? 0),
                'active'      => (bool) ($data['active'] ?? true),
            ]);
            $item->saveOrFail();

            $translations = $data['translations'] ?? [];
            if ($translations) {
                $item->translations()->delete();
                foreach ($translations as $locale => $fields) {
                    // 兼容两种格式：['zh-cn' => ['text' => '...']] 或 [['locale' => 'zh-cn', 'text' => '...']]
                    if (is_array($fields) && isset($fields['locale'])) {
                        $locale = $fields['locale'];
                    }
                    $text = is_array($fields) ? ($fields['text'] ?? '') : (string) $fields;
                    if (! empty($text)) {
                        $item->translations()->create(['locale' => $locale, 'text' => $text]);
                    }
                }
            }

            DB::commit();

            return $item;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除公告。
     *
     * @param  mixed  $item
     * @return void
     */
    public function destroy($item): void
    {
        $item->translations()->delete();
        $item->delete();
    }
}
