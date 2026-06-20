<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

class BaseService
{
    protected static $instance;

    /**
     * Get instance
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Get singleton instance
     */
    public static function getSingleton(): static
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new static;
    }
}
