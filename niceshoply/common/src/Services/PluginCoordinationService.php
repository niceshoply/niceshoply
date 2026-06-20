<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\PluginCoordination;
use NiceShoply\Common\Services\Fee\BalanceService;
use NiceShoply\Common\Services\Fee\Shipping;
use NiceShoply\Common\Services\Fee\Subtotal;
use NiceShoply\Common\Services\Fee\Tax;
use NiceShoply\Plugin\Repositories\PluginRepo;

/**
 * 插件协调（编排）服务
 *
 * 提供同类型插件的执行顺序排序、互斥判定与配置管理，
 * 供结账费用、价格计算等场景按配置顺序执行多个插件。
 */
class PluginCoordinationService extends BaseService
{
    /**
     * 缓存有效期（秒），默认 1 小时。
     */
    private const CACHE_TTL = 3600;

    /**
     * 核心结账费用类（固定顺序，不参与 orderfee 编排）。
     *
     * @var array<int, class-string>
     */
    private const CORE_FEE_CLASSES = [
        Subtotal::class,
        Tax::class,
        Shipping::class,
    ];

    /**
     * 余额抵扣费用类（始终最后执行）。
     */
    private const BALANCE_FEE_CLASS = BalanceService::class;

    /**
     * 从插件命名空间类名解析 plugin code（Plugin\{Code}\...）。
     *
     * @param  string  $class
     * @return string|null
     */
    public static function resolvePluginCodeFromClass(string $class): ?string
    {
        if (preg_match('/^Plugin\\\\([^\\\\]+)\\\\/', $class, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 按协调配置对结账 fee method 类重新排序。
     *
     * 规则：核心费用（小计/税/运费）保持在前 → orderfee 插件类按 sort_order → 其余未知类 → 余额抵扣最后。
     *
     * @param  array<int, class-string>  $classes
     * @return array<int, class-string>
     */
    public function sortFeeMethodClasses(array $classes): array
    {
        $corePresent  = [];
        $pluginByCode = [];
        $balance      = null;
        $unknown      = [];

        foreach ($classes as $class) {
            if (in_array($class, self::CORE_FEE_CLASSES, true)) {
                $corePresent[$class] = true;

                continue;
            }

            if ($class === self::BALANCE_FEE_CLASS) {
                $balance = $class;

                continue;
            }

            $pluginCode = self::resolvePluginCodeFromClass($class);
            if ($pluginCode) {
                $pluginByCode[$pluginCode] = $class;

                continue;
            }

            $unknown[] = $class;
        }

        $sortedPluginClasses = [];
        foreach ($this->getOrderedPlugins('orderfee') as $plugin) {
            $code = $plugin->code;
            if (isset($pluginByCode[$code])) {
                $sortedPluginClasses[] = $pluginByCode[$code];
                unset($pluginByCode[$code]);
            }
        }

        foreach ($pluginByCode as $class) {
            $sortedPluginClasses[] = $class;
        }

        $result = [];
        foreach (self::CORE_FEE_CLASSES as $coreClass) {
            if (isset($corePresent[$coreClass])) {
                $result[] = $coreClass;
            }
        }

        $result = array_merge($result, $unknown, $sortedPluginClasses);

        if ($balance) {
            $result[] = $balance;
        }

        return $result;
    }

    /**
     * 按协调配置依次执行价格类 Hook（model.sku.final_price.{code}），最后执行全局 Hook。
     *
     * @param  array{sku: mixed, price: float|int}  $data
     * @return array{sku: mixed, price: float|int}
     */
    public function applyPriceHookFilters(array $data): array
    {
        $applied = [];

        foreach ($this->getOrderedPlugins('price') as $plugin) {
            $code = $plugin->code;
            if ($this->shouldSkip('price', $code, $applied)) {
                continue;
            }

            $filtered = fire_hook_filter("model.sku.final_price.{$code}", $data);
            if (is_array($filtered)) {
                $data      = $filtered;
                $applied[] = $code;
            }
        }

        $filtered = fire_hook_filter('model.sku.final_price', $data);

        return is_array($filtered) ? $filtered : $data;
    }

    /**
     * 按配置顺序获取插件集合。
     *
     * @param  string  $type  插件类型（price、orderfee）
     * @return Collection 已排序的插件集合
     */
    public function getOrderedPlugins(string $type): Collection
    {
        $config  = $this->getConfig($type);
        $plugins = $this->getPluginsByType($type);

        if ($config?->sort_order && is_array($config->sort_order) && count($config->sort_order) > 0) {
            return $plugins->sortBy(function ($plugin) use ($config) {
                $position = array_search($plugin->code, $config->sort_order);

                return $position === false ? 999 : $position;
            })->values();
        }

        return $plugins;
    }

    /**
     * 获取指定插件类型的协调配置。
     *
     * @param  string  $type  插件类型
     * @return PluginCoordination|null
     */
    public function getConfig(string $type): ?PluginCoordination
    {
        $cacheKey = $this->getCacheKey($type);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type) {
            return PluginCoordination::where('type', $type)->first();
        });
    }

    /**
     * 根据协调规则判断当前插件是否应跳过执行。
     *
     * @param  string  $type  插件类型
     * @param  string  $current  当前插件 code
     * @param  array  $appliedPlugins  已应用的插件 code 列表
     * @return bool 应跳过返回 true
     */
    public function shouldSkip(string $type, string $current, array $appliedPlugins): bool
    {
        if (empty($appliedPlugins)) {
            return false;
        }

        $config = $this->getConfig($type);

        if (! $config) {
            return false;
        }

        return match ($config->exclusive_mode) {
            'first_only' => true,  // 已有插件应用，跳过其余全部
            'all_stack'  => false, // 全部叠加，永不跳过
            'custom'     => $this->hasExclusiveConflict($config, $current, $appliedPlugins),
            default      => false,
        };
    }

    /**
     * 判断当前插件与任一已应用插件是否存在互斥冲突。
     *
     * @param  PluginCoordination  $config  配置对象
     * @param  string  $current  当前插件 code
     * @param  array  $appliedPlugins  已应用的插件 code 列表
     * @return bool 存在冲突返回 true
     */
    public function hasExclusiveConflict(PluginCoordination $config, string $current, array $appliedPlugins): bool
    {
        $pairs = $config->exclusive_pairs ?? [];

        if (empty($pairs)) {
            return false;
        }

        foreach ($pairs as $pair) {
            if (is_array($pair) && in_array($current, $pair)) {
                // 当前插件位于某个互斥对中
                // 检查是否已有应用的插件也在该对中
                foreach ($appliedPlugins as $applied) {
                    if (in_array($applied, $pair)) {
                        return true; // 检测到冲突
                    }
                }
            }
        }

        return false;
    }

    /**
     * 清除指定类型的协调缓存。
     *
     * @param  string|null  $type  插件类型，null 表示全部类型
     * @return void
     */
    public function clearCache(?string $type = null): void
    {
        if ($type) {
            Cache::forget($this->getCacheKey($type));
        } else {
            // 清除全部插件协调缓存
            $types = PluginCoordination::pluck('type')->toArray();
            foreach ($types as $t) {
                Cache::forget($this->getCacheKey($t));
            }
        }
    }

    /**
     * 获取指定插件类型的缓存键。
     *
     * @param  string  $type
     * @return string
     */
    private function getCacheKey(string $type): string
    {
        return "plugin_coordination:{$type}";
    }

    /**
     * 从插件仓库按类型获取插件集合。
     *
     * @param  string  $type
     * @return Collection
     */
    private function getPluginsByType(string $type): Collection
    {
        $pluginRepo = PluginRepo::getInstance();

        return $pluginRepo->getBuilder(['type' => $type])->get();
    }

    /**
     * 创建或更新指定插件类型的协调配置。
     *
     * @param  string  $type
     * @param  array  $data
     * @return PluginCoordination
     */
    public function updateConfig(string $type, array $data): PluginCoordination
    {
        $config = PluginCoordination::updateOrCreate(
            ['type' => $type],
            [
                'sort_order'      => $data['sort_order'] ?? [],
                'exclusive_mode'  => $data['exclusive_mode'] ?? 'all_stack',
                'exclusive_pairs' => $data['exclusive_pairs'] ?? [],
            ]
        );

        $this->clearCache($type);

        return $config;
    }

    /**
     * 删除指定插件类型的协调配置。
     *
     * @param  string  $type
     * @return bool
     */
    public function deleteConfig(string $type): bool
    {
        $deleted = PluginCoordination::where('type', $type)->delete();

        if ($deleted) {
            $this->clearCache($type);
        }

        return (bool) $deleted;
    }
}
