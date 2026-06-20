<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Repositories;

use Illuminate\Support\Str;
use NiceShoply\Common\Repositories\SettingRepo;
use Throwable;

class ThemeRepo
{
    const SETTING_KEYS = [
        'menu_header_categories',
        'menu_header_catalogs',
        'menu_header_pages',
        'menu_header_specials',
        'menu_footer_categories',
        'menu_footer_catalogs',
        'menu_footer_pages',
        'menu_footer_specials',
        'home_categories',
    ];

    const JSON_SETTING_KEYS = [
        'slideshow',
        'home_hot_products',
    ];

    /**
     * @return self
     */
    public static function getInstance(): ThemeRepo
    {
        return new self;
    }

    /**
     * Get theme list from themes path.
     *
     * @return array
     */
    public function getListFromPath(): array
    {
        $path       = base_path('themes');
        $themePaths = glob($path.'/*');

        $themes = [];
        foreach ($themePaths as $themePath) {
            $theme    = trim(str_replace($path, '', $themePath), '/');
            $themes[] = [
                'code'     => $theme,
                'name'     => Str::studly($theme),
                'selected' => system_setting('theme') == $theme,
                'preview'  => $this->getPreviewPath($theme),
            ];
        }

        return $themes;
    }

    /**
     * @param  $settings
     * @return void
     * @throws Throwable
     */
    public function updateSetting($settings): void
    {
        foreach (self::SETTING_KEYS as $key) {
            $settings[$key] = is_array($settings[$key] ?? null) ? $settings[$key] : [];
        }

        foreach (self::JSON_SETTING_KEYS as $key) {
            if (isset($settings[$key]) && is_string($settings[$key])) {
                $decoded = json_decode($settings[$key], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $settings[$key] = $decoded;
                }
            }
            if (isset($settings[$key]) && ! is_array($settings[$key])) {
                $settings[$key] = [];
            }
        }

        SettingRepo::getInstance()->updateValues($settings);
    }

    /**
     * @param  string  $themeCode
     * @return string
     */
    private function getPreviewPath(string $themeCode): string
    {
        $path = theme_path($themeCode.'/public/images/preview.png');
        if (file_exists($path)) {
            return 'images/preview.png';
        }

        $path = theme_path($themeCode.'/public/images/preview.jpg');
        if (file_exists($path)) {
            return 'images/preview.jpg';
        }

        return '';
    }

    /**
     * Get all theme directories
     * @return array
     */
    public function getThemeDirs(): array
    {
        $path = base_path('themes');

        return glob($path.'/*', GLOB_ONLYDIR) ?: [];
    }

    /**
     * Read theme config.json file
     * @param  string  $dir
     * @return array
     * @throws \Exception
     */
    public function readConfig(string $dir): array
    {
        $configFile = $dir.'/config.json';
        if (! file_exists($configFile)) {
            throw new \Exception(__('console/themes.error_config_not_found', ['file' => $configFile]));
        }
        $config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(__('console/themes.error_config_invalid', ['file' => $configFile]));
        }

        return $config;
    }
}
