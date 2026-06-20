<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Middleware;

use Closure;
use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\RedirectRepo;
use Symfony\Component\HttpFoundation\Response;

/**
 * 前台 URL 重定向中间件：匹配规则后 301/302 跳转并累计命中。
 */
class RedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        // 跳过后台、API 与静态资源
        if ($request->is('console/*', 'admin/*', 'api/*', 'build/*', 'vendor/*', 'storage/*')) {
            return $next($request);
        }

        $repo = RedirectRepo::getInstance();
        $path = $repo->normalizePath($request->getPathInfo());

        $redirect = $repo->matchPath($path);
        if (! $redirect) {
            return $next($request);
        }

        $repo->recordHit($redirect);

        $target = trim($redirect->target_path);
        if ($target === '') {
            return $next($request);
        }

        if (! preg_match('#^https?://#i', $target)) {
            $target = url($repo->normalizePath($target));
        }

        $status = in_array((int) $redirect->status_code, [301, 302], true)
            ? (int) $redirect->status_code
            : 301;

        return redirect()->away($target, $status);
    }
}
