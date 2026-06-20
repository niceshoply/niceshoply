<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRecovery\Commands;

use Illuminate\Console\Command;
use Plugin\CartRecovery\Services\CartRecoveryService;

class RecoverCartCommand extends Command
{
    protected $signature = 'cart:recover';

    protected $description = 'Scan abandoned carts and send recovery notifications';

    public function handle(): int
    {
        if (! (bool) plugin_setting('cart_recovery', 'enabled', true)) {
            $this->warn('Cart recovery is disabled.');

            return self::SUCCESS;
        }

        $count = CartRecoveryService::getInstance()->scanAndNotify();
        $this->info("Cart recovery sent: {$count}");

        return self::SUCCESS;
    }
}
