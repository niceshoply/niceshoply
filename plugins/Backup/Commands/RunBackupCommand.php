<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Backup\Commands;

use Illuminate\Console\Command;
use Plugin\Backup\Services\BackupService;

class RunBackupCommand extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Run a database backup (mysqldump + gzip) into storage/app/backups';

    public function handle(): int
    {
        try {
            $file = BackupService::getInstance()->run();
            $this->info('Backup created: '.$file);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
