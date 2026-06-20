<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Middleware;

use Closure;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\EventTrackingService;
use NiceShoply\Common\Services\VisitTrackingService;

/**
 * 前台访问追踪中间件
 *
 * 记录会话访问与页面浏览事件；仅对 GET 非 AJAX 请求记录页面浏览，
 * 且可通过 system_setting('visit_tracking_enabled') 开关控制，默认开启。
 */
class VisitTrackingMiddleware
{
    /**
     * 访问追踪服务实例。
     */
    private VisitTrackingService $visitTrackingService;

    public function __construct()
    {
        $this->visitTrackingService = new VisitTrackingService;
    }

    /**
     * 处理请求。
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // 追踪开关（默认开启），便于在高并发或隐私合规场景下关闭
        if (! system_setting('visit_tracking_enabled', true)) {
            return $next($request);
        }

        $sessionId  = $request->session()->getId();
        $customerId = current_customer()?->id;

        $this->visitTrackingService->trackVisit($request, $sessionId, $customerId);

        // 仅 GET 且非 AJAX 记录页面浏览事件
        if ($request->isMethod('GET') && ! $request->ajax()) {
            (new EventTrackingService)->trackPageView($request);
        }

        return $next($request);
    }
}
