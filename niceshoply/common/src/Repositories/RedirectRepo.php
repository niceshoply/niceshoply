<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Redirect;

/**
 * URL 重定向数据访问层。
 */
class RedirectRepo extends BaseRepo
{
    protected string $model = Redirect::class;

    private const CACHE_KEY = 'seo:active_redirects';

    private const CACHE_TTL = 300;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'source_path', 'type' => 'input', 'label' => trans('console/redirect.source_path')],
            ['name' => 'active', 'type' => 'select', 'label' => trans('console/common.active'), 'options' => [
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
        $builder = Redirect::query();

        if (! empty($filters['source_path'])) {
            $builder->where('source_path', 'like', '%'.$filters['source_path'].'%');
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder->orderByDesc('id');
    }

    /**
     * 按请求路径匹配启用的重定向规则。
     */
    public function matchPath(string $path): ?Redirect
    {
        $path = $this->normalizePath($path);
        $map  = $this->getActiveMap();

        return $map[$path] ?? null;
    }

    /**
     * 递增命中次数并清除缓存映射中的 hits（异步友好，直接 DB increment）。
     */
    public function recordHit(Redirect $redirect): void
    {
        Redirect::query()->where('id', $redirect->id)->increment('hits');
    }

    /**
     * 保存/更新/删除后刷新缓存。
     */
    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create($data): mixed
    {
        $data['source_path'] = $this->normalizePath((string) ($data['source_path'] ?? ''));
        $result              = parent::create($data);
        $this->flushCache();

        return $result;
    }

    /**
     * @param  mixed  $item
     * @param  array<string, mixed>  $data
     */
    public function update($item, $data): mixed
    {
        if (isset($data['source_path'])) {
            $data['source_path'] = $this->normalizePath((string) $data['source_path']);
        }
        $result = parent::update($item, $data);
        $this->flushCache();

        return $result;
    }

    /**
     * @param  mixed  $item
     */
    public function destroy(mixed $item): void
    {
        parent::destroy($item);
        $this->flushCache();
    }

    /**
     * @return array<string, Redirect>
     */
    private function getActiveMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $map = [];
            Redirect::query()->where('active', true)->get()->each(function (Redirect $redirect) use (&$map) {
                $map[$this->normalizePath($redirect->source_path)] = $redirect;
            });

            return $map;
        });
    }

    /**
     * 统一路径格式：以 / 开头，去除尾部斜杠（根路径除外）。
     */
    public function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return rtrim($path, '/');
    }
}
