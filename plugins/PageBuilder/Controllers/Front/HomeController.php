<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Controllers\Front;

use Exception;
use NiceShoply\Front\Controllers\BaseController;
use Plugin\PageBuilder\Services\DesignService;

class HomeController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $isDesignMode = request()->get('design');
        $modules      = plugin_setting('page_builder', 'modules');

        if (empty($modules) || empty($modules['modules'])) {
            if ($isDesignMode) {
                return view('PageBuilder::front.home', [
                    'modules' => [],
                    'device'  => request()->get('device', 'pc'),
                ]);
            }

            return app(\NiceShoply\Front\Controllers\HomeController::class)->index();
        }

        $processedModules = [];
        foreach ($modules['modules'] as $module) {
            $moduleCode = $module['code'] ?? '';
            $content    = $module['content'] ?? [];

            if ($moduleCode && $content) {
                $processedModules[] = [
                    'code'      => $moduleCode,
                    'content'   => DesignService::getInstance()->handleModuleContent($moduleCode, $content),
                    'module_id' => $module['module_id'] ?? 'module-'.uniqid(),
                    'name'      => $module['name'] ?? '',
                    'view_path' => $module['view_path'] ?? '',
                ];
            }
        }

        return view('PageBuilder::front.home', [
            'modules' => $processedModules,
            'device'  => request()->get('device', 'pc'),
        ]);
    }
}
