<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Repositories;

use Exception;

class LocaleRepo extends BaseRepo
{
    /**
     * Get console languages by path.
     *
     * @throws Exception
     */
    public function getConsoleLanguages(): array
    {
        $items = [];
        foreach (console_lang_path_codes() as $localeCode) {
            $langFile = lang_path("/$localeCode/common/base.php");
            if (! is_file($langFile)) {
                throw new Exception("File ($langFile) not exist!");
            }
            $baseData = require $langFile;
            $name     = $baseData['name'] ?? $localeCode;
            $items[]  = [
                'code'  => $localeCode,
                'name'  => $name,
                'image' => "images/flag/$localeCode.png",
            ];
        }

        return $items;
    }

    /**
     * @param  $code
     * @return array
     * @throws Exception
     */
    public function getLocaleByCode($code): array
    {
        $locales = $this->getConsoleLanguages();

        $locale = collect($locales)->where('code', $code)->first();
        if (empty($locale)) {
            $locale = collect($locales)->first();
            app()->setLocale($locale['code']);
        }

        return $locale;
    }
}
