<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use NiceShoply\Plugin\Core\Plugin;
use NiceShoply\Plugin\Repositories\SettingRepo;
use NiceShoply\Plugin\Resources\PluginResource;
use NiceShoply\Plugin\Services\PluginService;
use Throwable;

class PluginController
{
    /**
     * Get all plugins.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $allPlugins = app('plugin')->getPlugins();
        $type       = $request->get('type');

        $typeCounts = [];
        foreach (Plugin::TYPES as $pluginType) {
            $typeCounts[$pluginType] = $allPlugins->where('type', $pluginType)->count();
        }
        $typeCounts['all'] = $allPlugins->count();

        $plugins = $allPlugins;
        if ($type && in_array($type, Plugin::TYPES)) {
            $plugins = $plugins->where('type', $type);
        }

        $data = [
            'types'      => Plugin::TYPES,
            'type'       => $type,
            'plugins'    => array_values(PluginResource::collection($plugins)->jsonSerialize()),
            'typeCounts' => $typeCounts,
        ];

        return nice_view('plugin::plugins.index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $code   = $request->get('code');
            $plugin = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->installPlugin($plugin);
            Artisan::call('view:clear');

            return json_success(console_trans('common.saved_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  $code
     * @return mixed
     */
    public function destroy($code): mixed
    {
        try {
            $plugin = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->uninstallPlugin($plugin);
            Artisan::call('view:clear');

            return json_success(console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Show plugin details (redirects to edit page).
     *
     * @param  $code
     * @return View
     */
    public function show($code): View
    {
        return $this->edit($code);
    }

    /**
     * @param  $code
     * @return View
     */
    public function edit($code): View
    {
        try {
            $plugin     = app('plugin')->getPluginOrFail($code);
            $customView = $plugin->getFieldView();
            $data       = [
                'plugin'     => $plugin,
                'fields'     => $plugin->getFields(),
                'customView' => $customView,
            ];

            return nice_view('plugin::plugins.form', $data);
        } catch (Exception $e) {
            $plugin = app('plugin')->getPlugin($code);
            $data   = [
                'error'       => $e->getMessage(),
                'plugin_code' => $code,
                'plugin'      => $plugin,
            ];

            return nice_view('plugin::plugins.error', $data);
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $code
     * @return mixed
     * @throws Throwable
     */
    public function update(Request $request, string $code): mixed
    {
        $fields = $request->all();
        $plugin = app('plugin')->getPluginOrFail($code);
        if (method_exists($plugin, 'validateFields')) {
            $validator = $plugin->validateFields($fields);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }
        SettingRepo::getInstance()->updateValues($fields, $code);
        Artisan::call('view:clear');
        $currentUrl = console_route('plugins.edit', [$code]);

        return redirect($currentUrl)
            ->with('instance', $plugin)
            ->with('success', console_trans('common.updated_success'));
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function updateStatus(Request $request): mixed
    {
        try {
            $code    = $request->get('code');
            $enabled = $request->get('enabled');
            app('plugin')->getPluginOrFail($code);
            SettingRepo::getInstance()->updatePluginValue($code, 'active', $enabled);
            Artisan::call('view:clear');

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 手动执行插件数据填充器（Seeders）。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function runSeeders(Request $request): mixed
    {
        try {
            $code      = $request->get('code');
            $clearData = (bool) $request->get('clear_data', false);
            $plugin    = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->runSeeders($plugin, $clearData);

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }

    /**
     * 重置插件数据库：回滚 → 迁移 → 填充。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function reset(Request $request): mixed
    {
        try {
            $code      = $request->get('code');
            $clearData = (bool) $request->get('clear_data', false);
            $plugin    = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->resetPlugin($plugin, $clearData);
            Artisan::call('view:clear');

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }
}
