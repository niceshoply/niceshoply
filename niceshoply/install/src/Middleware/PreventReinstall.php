<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Install\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 防止已安装站点被再次触发安装流程（预认证重装接管防护）。
 *
 * 背景：安装相关 POST 端点（driver_detect / connected / complete）原本未校验
 * 安装状态，攻击者可在生产站点对 /install/complete 发起请求重新执行 setup，
 * 覆盖管理员账号与数据库配置，造成接管。此中间件在系统已安装时统一拦截：
 *  - 非 JSON 请求：重定向回前台首页；
 *  - JSON/AJAX 请求：返回 403，避免泄露安装界面信息。
 */
class PreventReinstall
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (installed()) {
            if ($request->expectsJson()) {
                abort(403, 'Application is already installed.');
            }

            return redirect(safe_front_home_url());
        }

        return $next($request);
    }
}
