<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Logging;

use Illuminate\Support\Str;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RequestContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $request = request();

        $extra = array_merge($record->extra, [
            'request_id'  => $request?->header('X-Request-ID') ?? Str::uuid()->toString(),
            'user_id'     => $this->getUserId(),
            'user_type'   => $this->getUserType(),
            'session_id'  => session()?->getId() ?? null,
            'ip'          => $request?->ip(),
            'url'         => $request?->fullUrl(),
            'method'      => $request?->method(),
            'user_agent'  => $request?->userAgent(),
            'environment' => config('app.env'),
        ]);

        return $record->with(extra: $extra);
    }

    private function getUserId(): ?int
    {
        if (auth('admin')->check()) {
            return auth('admin')->id();
        }
        if (auth('customer')->check()) {
            return auth('customer')->id();
        }

        return null;
    }

    private function getUserType(): string
    {
        if (auth('admin')->check()) {
            return 'admin';
        }
        if (auth('customer')->check()) {
            return 'customer';
        }

        return 'guest';
    }
}
