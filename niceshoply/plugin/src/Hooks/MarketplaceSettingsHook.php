<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Hooks;

class MarketplaceSettingsHook
{
    /**
     * Initialize marketplace settings hooks
     *
     * @return void
     */
    public function init(): void
    {
        $this->registerSettingTab();
        $this->registerSettingFields();
    }

    /**
     * Register setting tab navigation
     *
     * @return void
     */
    private function registerSettingTab(): void
    {
        listen_blade_insert('console.settings.tab.nav.bottom', function ($data) {
            return view('plugin::console.settings.marketplace_nav')->render();
        });
    }

    /**
     * Register setting tab content
     *
     * @return void
     */
    private function registerSettingFields(): void
    {
        listen_blade_insert('console.settings.tab.pane.bottom', function ($data) {
            return view('plugin::console.settings.marketplace_pane')->render();
        });
    }
}
