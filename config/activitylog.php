<?php
/**
 * Activity Log Configuration
 *
 * @see https://spatie.be/docs/laravel-activitylog
 */

return [

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    'delete_records_older_than_days' => env('ACTIVITY_LOG_RETENTION_DAYS', 90),

    'default_log_name' => 'admin',

    'default_auth_driver' => 'admin',

    'subject_returns_soft_deleted_models' => true,

    'activity_model' => \NiceShoply\Common\Models\AuditActivity::class,

    'table_name' => 'activity_log',

    'database_connection' => env('ACTIVITY_LOG_DB_CONNECTION'),

];
