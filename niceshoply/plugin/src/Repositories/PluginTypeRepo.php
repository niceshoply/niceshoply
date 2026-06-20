<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Repositories;

use NiceShoply\Plugin\Core\Plugin;

class PluginTypeRepo
{
    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self;
    }

    /**
     * Get all plugin types
     *
     * @return array
     */
    public function getTypes(): array
    {
        return Plugin::TYPES;
    }

    /**
     * Get all plugin types and their corresponding menu configurations
     *
     * @return array
     */
    public function getTypeMenus(): array
    {
        $routes = [];

        foreach ($this->getTypes() as $type) {
            $routes[] = [
                'route'  => 'plugins.index',
                'title'  => __('console/plugin.'.$type),
                'url'    => console_route('plugins.index', ['type' => $type]),
                'params' => ['type' => $type],
            ];
        }

        return fire_hook_filter('console.plugin.type.menus', $routes);
    }

    /**
     * Check if the type is valid
     *
     * @param  string  $type
     * @return bool
     */
    public function isValidType(string $type): bool
    {
        return in_array($type, $this->getTypes());
    }
}
