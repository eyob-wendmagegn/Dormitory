<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'google' => [
            'driver' => 'google',
            'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', storage_path('app/google/service-account.json')),
            'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
            'folder_name' => env('GOOGLE_DRIVE_FOLDER_NAME', 'Dormitory_system'),
            'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Database Backup & Restore Configuration
    |--------------------------------------------------------------------------
    |
    | Stores binary paths, connection selection, retention window, and local
    | scratch storage path for PostgreSQL backup and restore automation.
    |
    */

    'backup' => [
        'connection' => env('BACKUP_DB_CONNECTION', 'pgsql'),
        'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
        'psql_path' => env('PSQL_PATH', 'psql'),
        'temp_path' => env('BACKUP_TEMP_PATH', storage_path('app/backups')),
        'google_disk' => 'google',
        'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
        'pg_dump_timeout' => (int) env('BACKUP_DUMP_TIMEOUT', 600),
        'psql_timeout' => (int) env('BACKUP_RESTORE_TIMEOUT', 1200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
