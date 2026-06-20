<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Interfaces;

interface Translator
{
    public function translate($from, $to, $text): string;

    public function batchTranslate($from, $to, $texts): array;

    public function mapCode($code): string;
}
