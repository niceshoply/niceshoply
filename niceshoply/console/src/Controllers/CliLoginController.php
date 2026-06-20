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
use NiceShoply\Common\Models\Admin;

/**
 * 命令行一次性登录控制器（运维场景）。
 *
 * 工作流：
 *  1. 运维在服务器执行 `php artisan admin:cli-login {email?}` 生成带签名的临时链接；
 *  2. 在浏览器打开该链接，本控制器经 `signed` 中间件校验签名与有效期后免密登录后台。
 *
 * 安全保证：
 *  - 链接由 APP_KEY 签名，无法伪造；
 *  - 默认 15 分钟内有效（可在命令行调整），过期即失效；
 *  - 被禁用账号（active=false）拒绝登录；
 *  - 登录后立即重建 session id，避免会话固定攻击。
 */
class CliLoginController extends BaseController
{
    /**
     * 校验签名链接并登录指定后台账号。
     *
     * @param  Request  $request
     * @param  Admin  $admin  路由模型绑定的后台账号
     * @return mixed
     */
    public function login(Request $request, Admin $admin): mixed
    {
        // 已禁用账号不允许通过命令行链接登录
        if (! $admin->active) {
            abort(403, console_trans('cli_login.disabled'));
        }

        auth('admin')->login($admin);

        // 防会话固定：登录成功后重建 session id
        $request->session()->regenerate();

        return redirect(console_route('home.index'))
            ->with('success', console_trans('cli_login.success'));
    }
}
