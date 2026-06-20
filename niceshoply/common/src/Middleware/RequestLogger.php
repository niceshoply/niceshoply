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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();
        $request->headers->set('X-Request-ID', $requestId);

        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('json')->info('api.request', [
            'request_id'    => $requestId,
            'method'        => $request->method(),
            'url'           => $request->fullUrl(),
            'route'         => $request->route()?->getName(),
            'status_code'   => $response->getStatusCode(),
            'duration_ms'   => $duration,
            'ip'            => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'request_size'  => $request->header('Content-Length', 0),
            'response_size' => strlen($response->getContent()),
        ]);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
