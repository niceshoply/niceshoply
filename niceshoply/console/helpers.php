<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Str;
use NiceShoply\Console\Repositories\LocaleRepo;
use NiceShoply\Console\Services\TranslatorService;

if (! function_exists('console_name')) {
    /**
     * Admin console name
     *
     * @return string
     */
    function console_name(): string
    {
        return system_setting('console_name', 'console') ?: 'console';
    }
}

if (! function_exists('console_locales')) {
    /**
     * Get available locales
     *
     * @return array
     * @throws Exception
     */
    function console_locales(): array
    {
        return LocaleRepo::getInstance()->getConsoleLanguages();
    }
}

if (! function_exists('console_locale_code')) {
    /**
     * Get console locale code
     *
     * @return string
     * @throws Exception
     */
    function console_locale_code(): string
    {
        return current_admin()->locale ?? console_session_locale();
    }
}

if (! function_exists('console_session_locale')) {
    /**
     * Get console locale code from session
     *
     * @return string
     * @throws Exception
     */
    function console_session_locale(): string
    {
        return session('console_locale', setting_locale_code());
    }
}

if (! function_exists('current_console_locale')) {
    /**
     * Get current locale code.
     *
     * @return array
     * @throws Exception
     */
    function current_console_locale(): array
    {
        return LocaleRepo::getInstance()->getLocaleByCode(console_locale_code());
    }
}

if (! function_exists('console_locale_direction')) {
    /**
     * Get locale direction for console admin.
     *
     * @return string
     * @throws Exception
     */
    function console_locale_direction(): string
    {
        $localeCode = console_locale_code();
        $rtlCodes   = array_keys(\NiceShoply\Common\Repositories\LocaleRepo::getRtlLanguages());

        return in_array($localeCode, $rtlCodes) ? 'rtl' : 'ltr';
    }
}

if (! function_exists('console_lang_path_codes')) {
    /**
     * Get all console languages
     *
     * @return array
     */
    function console_lang_path_codes(): array
    {
        $packages = language_codes();

        $consoleLangCodes = collect($packages)->filter(function ($code) {
            return file_exists(lang_path("{$code}/console"));
        })->toArray();

        return array_values($consoleLangCodes);
    }
}

if (! function_exists('console_trans')) {
    /**
     * @param  $key
     * @param  array  $replace
     * @param  $locale
     * @return mixed
     */
    function console_trans($key = null, array $replace = [], $locale = null): mixed
    {
        return trans('console/'.$key, $replace, $locale);
    }
}

if (! function_exists('console_route')) {
    /**
     * Get backend console route
     *
     * @param  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function console_route($name, mixed $parameters = [], bool $absolute = true): string
    {
        $consoleName = console_name();
        try {
            return route($consoleName.'.'.$name, $parameters, $absolute);
        } catch (\Exception $e) {
            try {
                return route($consoleName.'.dashboard.index');
            } catch (\Exception $e2) {
                return '/'.$consoleName;
            }
        }

    }
}

if (! function_exists('current_admin')) {
    /**
     * get current admin user.
     */
    function current_admin(): mixed
    {
        return auth('admin')->user();
    }
}

if (! function_exists('is_admin')) {
    /**
     * Check if current is admin console
     * @return bool
     */
    function is_admin(): bool
    {
        $adminName = console_name();
        $uri       = request()->getRequestUri();
        if (Str::startsWith($uri, "/{$adminName}")) {
            return true;
        }

        return false;
    }
}

if (! function_exists('dashboard_url')) {
    /**
     * Get dashboard url
     * like https://www.niceshoply.com/install/dashboard.jpg?edition=community&version=1.0.0&build_date=20250909
     *
     * @return string
     */
    function dashboard_url(): string
    {
        $params = [
            'base_url'   => console_route('home.index'),
            'edition'    => config('niceshoply.edition'),
            'version'    => config('niceshoply.version'),
            'build_date' => config('niceshoply.build'),
        ];
        $urlParams = http_build_query($params);

        return config('niceshoply.api_url').'/install/dashboard.jpg?'.$urlParams;
    }
}

if (! function_exists('default_locale_class')) {
    /**
     * Get default locale class name for console admin.
     * @param  $localeCode
     * @return string
     */
    function default_locale_class($localeCode): string
    {
        return is_setting_locale($localeCode) ? 'border border-2 border-danger-subtle ' : '';
    }
}

if (! function_exists('has_translator')) {
    /**
     * Check if the translator is enabled.
     *
     * @return bool
     */
    function has_translator(): bool
    {
        try {
            return TranslatorService::getTranslator() !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (! function_exists('has_set_value')) {
    /**
     * Verify if any fields in the current parameters have been assigned a value.
     *
     * @param  $parameters
     * @return bool
     */
    function has_set_value($parameters): bool
    {
        $ignoreList = ['page', 'per_page', 'sort', 'order'];
        foreach ($parameters as $key => $value) {
            if (in_array($key, $ignoreList)) {
                continue;
            }
            if (! is_null($value)) {
                return true;
            }
        }

        return false;
    }
}
