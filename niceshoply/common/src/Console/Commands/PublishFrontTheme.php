<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishFrontTheme extends Command
{
    protected $signature = 'inno:publish-theme';

    protected $description = 'Publish default theme for frontend.';

    public function handle(): void
    {
        Artisan::call('vendor:publish', [
            '--provider' => 'NiceShoply\Front\FrontServiceProvider',
            '--tag'      => 'views',
        ]);
        echo Artisan::output();
    }
}
