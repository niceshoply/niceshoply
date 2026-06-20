<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\Customer\SocialRepo;
use NiceShoply\Common\Repositories\SettingRepo;
use Throwable;

class SocialController extends BaseController
{
    public function index()
    {
        $data = [
            'providers' => SocialRepo::getInstance()->getProviders(),
            'socials'   => system_setting('social', []),
        ];

        return nice_view('console::socials.index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        try {
            $data = $request->all();
            SettingRepo::getInstance()->updateSystemValue('social', $data);

            return update_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
