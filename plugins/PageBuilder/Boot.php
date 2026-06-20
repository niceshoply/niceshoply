<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    /**
     * Flag to prevent duplicate initialization
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * @return void
     */
    public function init(): void
    {
        // Prevent duplicate initialization
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        $this->addConsoleMenus();
        $this->addPageDesignButton();
    }

    /**
     * @return void
     */
    private function addConsoleMenus(): void
    {
        listen_hook_filter('console.component.sidebar.design.routes', function ($data) {
            $consolePath = function_exists('console_name') ? console_name() : 'console';

            $data[] = [
                'route'           => 'pbuilder.index',
                'url'             => '/'.$consolePath.'/pbuilder',
                'title'           => trans('PageBuilder::route.title'),
                'icon'            => 'heroicon-o-paint-brush',
                'blank'           => true,
                'skip_permission' => true,
            ];

            return $data;
        });
    }

    /**
     * Add design button to page list
     *
     * @return void
     */
    private function addPageDesignButton(): void
    {
        listen_blade_insert('console.page.list.table.row.actions.before', function ($data) {
            $item = $data['item'] ?? null;
            if ($item) {
                return view('PageBuilder::console.pages.design-button', ['item' => $item])->render();
            }

            return null;
        });
    }
}
