<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => 'Data Backup',
    'keep_count'     => 'Retention count',
    'mysqldump_path' => 'mysqldump path (blank = system PATH)',

    'title'          => 'Database backup',
    'tip'            => 'Click "Backup now" to run mysqldump + gzip; files go to storage/app/backups. Cron-ready: php artisan backup:run',
    'run'            => 'Backup now',
    'created'        => 'Backup created: :file',
    'deleted'        => 'Deleted',
    'delete_failed'  => 'Delete failed',
    'confirm_del'    => 'Delete this backup?',

    'name'           => 'File',
    'size'           => 'Size',
    'time'           => 'Time',
    'download'       => 'Download',
    'del'            => 'Delete',
    'no_data'        => 'No backups',
];
