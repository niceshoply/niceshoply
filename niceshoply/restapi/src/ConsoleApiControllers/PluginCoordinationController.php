<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\PluginCoordinationService;

/**
 * 插件协调（编排）后台 API 控制器
 *
 * 提供费用类/价格类插件的执行顺序与互斥规则的查看与配置接口。
 */
class PluginCoordinationController extends BaseController
{
    /**
     * 支持协调的插件类型。
     */
    private const SUPPORTED_TYPES = ['price', 'orderfee'];

    /**
     * 展示插件协调配置。
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $coordinationService = app(PluginCoordinationService::class);
        $pluginManager       = app('plugin');

        $configs = [];

        foreach (self::SUPPORTED_TYPES as $type) {
            $config  = $coordinationService->getConfig($type);
            $plugins = $pluginManager->getPlugins()
                ->filter(fn ($plugin) => $plugin->getType() === $type)
                ->map(fn ($plugin) => [
                    'code' => $plugin->getCode(),
                    'name' => $plugin->getLocaleName(),
                ])
                ->values();

            $configs[$type] = [
                'plugins'         => $plugins,
                'sort_order'      => $config?->getSortOrder() ?? [],
                'exclusive_mode'  => $config?->getExclusiveMode() ?? 'all_stack',
                'exclusive_pairs' => $config?->getExclusivePairs() ?? [],
            ];
        }

        return read_json_success([
            'configs'         => $configs,
            'types'           => self::SUPPORTED_TYPES,
            'exclusive_modes' => ['first_only', 'all_stack', 'custom'],
        ]);
    }

    /**
     * 更新插件协调配置。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function update(Request $request): mixed
    {
        $request->validate([
            'type'              => 'required|in:price,orderfee',
            'sort_order'        => 'array',
            'sort_order.*'      => 'string',
            'exclusive_mode'    => 'required|in:first_only,all_stack,custom',
            'exclusive_pairs'   => 'array',
            'exclusive_pairs.*' => 'array',
        ]);

        try {
            $coordinationService = app(PluginCoordinationService::class);

            $config = $coordinationService->updateConfig($request->input('type'), [
                'sort_order'      => $request->input('sort_order', []),
                'exclusive_mode'  => $request->input('exclusive_mode'),
                'exclusive_pairs' => $request->input('exclusive_pairs', []),
            ]);

            return update_json_success($config);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
