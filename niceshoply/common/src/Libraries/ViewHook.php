<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Libraries;

class ViewHook
{
    /**
     * @return self
     */
    public static function getInstance(): ViewHook
    {
        return new self;
    }

    /**
     * @param  $trace
     * @return string
     */
    public function getHookName($trace): string
    {
        $class = $trace[1]['class'] ?? '';
        if (empty($class)) {
            return '';
        }

        $method = strtolower($trace[1]['function'] ?? '');
        if (empty($method)) {
            return '';
        }

        if (! str_starts_with($class, 'NiceShoply')) {
            return '';
        }

        $class = str_replace(['NiceShoply\\', 'Controllers\\', 'Controller'], '', $class);
        $class = strtolower(str_replace('\\', '.', $class));

        return "$class.$method";
    }
}
