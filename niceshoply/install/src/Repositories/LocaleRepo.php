<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Install\Repositories;

use Exception;
use NiceShoply\Console\Repositories\BaseRepo;

class LocaleRepo extends BaseRepo
{
    /**
     * Get console languages by path.
     *
     * @throws Exception
     */
    public function getInstallLanguages(): array
    {
        $items = [];
        foreach (install_lang_path_codes() as $localeCode) {
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
}
