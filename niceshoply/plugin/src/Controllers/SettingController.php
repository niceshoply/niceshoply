<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Controllers;

use NiceShoply\Common\Repositories\SettingRepo;
use NiceShoply\Console\Controllers\BaseController;

class SettingController extends BaseController
{
    public function index()
    {
        return view('plugin::console.settings.index');
    }

    public function update()
    {
        try {
            SettingRepo::getInstance()->updateValues(request()->all());

            return back()->with('success', __('common.updated_successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
