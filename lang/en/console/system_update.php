<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'    => 'System Update',
    'subtitle' => 'Check and install the latest official release online',

    'current_version' => 'Current Version',
    'latest_version'  => 'Latest Version',
    'build'           => 'Build',
    'edition'         => 'Edition',
    'released_at'     => 'Released At',
    'changelog'       => 'Changelog',
    'package_size'    => 'Package Size',

    'check_update'          => 'Check for Updates',
    'checking'              => 'Checking…',
    'check_done'            => 'Check completed',
    'check_failed'          => 'Failed to check for updates (HTTP :code)',
    'up_to_date'            => 'You are on the latest version',
    'new_version_available' => 'New version :version available',

    'start_update'    => 'Update Now',
    'start_queued'    => 'Upgrade started, please keep this page open',
    'updating'        => 'Updating…',
    'no_update'       => 'No update available',
    'already_running' => 'An upgrade is already running, please wait',
    'disabled'        => 'Online upgrade is disabled',
    'no_domain_token' => 'Domain token is not bound. Please authorize in the Marketplace first.',

    'confirm_update'     => 'Upgrade to :version? The site will enter maintenance mode during the upgrade.',
    'do_not_close'       => 'Upgrade in progress. Do not close or refresh this page, and avoid using the admin panel meanwhile.',
    'backup_warning'     => 'It is strongly recommended to back up your database and source code first. Files are backed up automatically for rollback, but the database is not.',
    'maintenance_notice' => 'The site will briefly enter maintenance mode during the upgrade; visitors will see a maintenance page.',

    'last_upgrade'      => 'Last Upgrade',
    'last_upgrade_none' => 'No upgrade record yet',

    'step_queued'      => 'Queued for upgrade',
    'step_start'       => 'Starting upgrade',
    'step_download'    => 'Downloading package…',
    'step_verify'      => 'Verifying package integrity…',
    'step_extract'     => 'Extracting package…',
    'step_maintenance' => 'Entering maintenance mode…',
    'step_backup'      => 'Backing up original files…',
    'step_apply'       => 'Applying program files…',
    'step_migrate'     => 'Running database migrations…',
    'step_cache'       => 'Rebuilding caches…',
    'step_reload'      => 'Reloading runtime…',
    'step_done'        => 'Upgrade completed',

    'success_done' => 'Successfully upgraded to :version',
    'rolling_back' => 'Upgrade failed, rolling back to the previous version…',

    'log_downloaded'  => 'Package downloaded (:size)',
    'log_checksum_ok' => 'Integrity check passed',
    'log_extracted'   => 'Package extracted',
    'log_backed_up'   => 'Backed up :count files',
    'log_applied'     => 'Applied :count files',

    'download_failed'      => 'Package download failed (HTTP :code)',
    'download_empty'       => 'Downloaded package is empty',
    'size_mismatch'        => 'Package size does not match the official value',
    'checksum_failed'      => 'Package checksum failed (SHA256 mismatch); it may be corrupted or tampered with',
    'extract_failed'       => 'Failed to extract the package',
    'php_required'         => 'This version requires PHP :require, current is :current',
    'min_version_required' => 'This package requires a minimum current version of :min, please upgrade incrementally',

    'status_idle'    => 'Idle',
    'status_queued'  => 'Queued',
    'status_running' => 'Updating',
    'status_success' => 'Success',
    'status_failed'  => 'Failed',

    'view_logs' => 'View Logs',
    'refresh'   => 'Refresh',
];
