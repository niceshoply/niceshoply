<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Logging;

use Illuminate\Log\Logger;
use Monolog\Processor\WebProcessor;

class ContextualLogTap
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        $monolog->pushProcessor(new RequestContextProcessor);
        $monolog->pushProcessor(new WebProcessor);
    }
}
