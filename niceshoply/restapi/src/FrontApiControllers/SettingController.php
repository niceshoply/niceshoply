<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use NiceShoply\Common\Models\Announcement;

class SettingController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $settings = setting('system');

        $settings['locales']    = locales()->select(['name', 'code']);
        $settings['currencies'] = currencies()->select(['name', 'code']);

        return read_json_success($settings);
    }

    /**
     * 获取启用中的顶部公告（前台展示）。
     *
     * @return mixed
     */
    public function announcements(): mixed
    {
        return read_json_success(Announcement::getActiveItems());
    }
}
