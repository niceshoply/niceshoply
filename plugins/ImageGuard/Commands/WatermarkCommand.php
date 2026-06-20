<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ImageGuard\Commands;

use Illuminate\Console\Command;
use Plugin\ImageGuard\Services\ImageGuardService;

class WatermarkCommand extends Command
{
    protected $signature = 'image:watermark {dir : Relative dir under storage/app/public}';

    protected $description = 'Batch add watermark and compress images in a storage/public subfolder';

    public function handle(): int
    {
        $dir = (string) $this->argument('dir');
        $result = ImageGuardService::getInstance()->processDirectory($dir);

        $this->info("Processed: {$result['processed']}, Failed: {$result['failed']}");
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }

        return self::SUCCESS;
    }
}
