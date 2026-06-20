<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

/**
 * 插件协调（编排）模型
 *
 * 用于配置同类型插件（如价格类 price、订单费用类 orderfee）同时启用时的
 * 执行顺序与互斥规则，避免多个插件叠加时结果不可控。
 */
class PluginCoordination extends BaseModel
{
    protected $table = 'plugin_coordinations';

    protected $fillable = [
        'type',
        'sort_order',
        'exclusive_mode',
        'exclusive_pairs',
    ];

    protected $casts = [
        'sort_order'      => 'array',
        'exclusive_pairs' => 'array',
    ];

    /**
     * 获取排序数组。
     *
     * @return array
     */
    public function getSortOrder(): array
    {
        return $this->sort_order ?? [];
    }

    /**
     * 获取互斥模式。
     *
     * @return string
     */
    public function getExclusiveMode(): string
    {
        return $this->exclusive_mode ?? 'all_stack';
    }

    /**
     * 获取互斥插件对。
     *
     * @return array
     */
    public function getExclusivePairs(): array
    {
        return $this->exclusive_pairs ?? [];
    }

    /**
     * 是否为「仅第一个生效」模式。
     *
     * @return bool
     */
    public function isFirstOnlyMode(): bool
    {
        return $this->exclusive_mode === 'first_only';
    }

    /**
     * 是否为「全部叠加」模式。
     *
     * @return bool
     */
    public function isAllStackMode(): bool
    {
        return $this->exclusive_mode === 'all_stack';
    }

    /**
     * 是否为「自定义互斥」模式。
     *
     * @return bool
     */
    public function isCustomMode(): bool
    {
        return $this->exclusive_mode === 'custom';
    }
}
