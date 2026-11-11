<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--type=Automatic : Backup trigger type (Automatic, Manual, Pre-Restore Safety)}
                            {--no-prune : Skip 30-day Google Drive retention pruning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export PostgreSQL database to compressed .sql.gz, upload to Google Drive, and enforce 30-day retention policy';

    public function handle(DatabaseBackupService $backupService): int
    {
        $type = (string) $this->option('type');
        $allowedTypes = [
            BackupLog::TRIGGER_AUTOMATIC,
            BackupLog::TRIGGER_MANUAL,
            BackupLog::TRIGGER_PRE_RESTORE,
        ];

        if (! in_array($type, $allowedTypes, true)) {
            $this->error("Invalid trigger type '{$type}'. Allowed values: ".implode(', ', $allowedTypes));

            return self::INVALID;
        }

        $prune = ! (bool) $this->option('no-prune');
        $this->info("Starting [{$type}] PostgreSQL database backup...");

        try {
            $backup = $backupService->createBackup($type, null, $prune);

            $this->info(sprintf(
                'Backup completed successfully: %s (%s) [Google Drive ID: %s]',
                $backup->file_name,
                $backup->formatted_size,
                $backup->google_drive_file_id ?? 'N/A'
            ));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('PostgreSQL backup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
