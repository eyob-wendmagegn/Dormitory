<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RestoreDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore
                            {--backup-id= : BackupLog ID to download from Google Drive and restore}
                            {--file= : Local .sql.gz or .sql file path to restore}
                            {--skip-safety : Skip taking a Pre-Restore Safety backup before overwriting}
                            {--force : Proceed without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely restore PostgreSQL database from Google Drive backup history or local .sql/.sql.gz archive';

    public function handle(DatabaseBackupService $backupService): int
    {
        $backupId = $this->option('backup-id');
        $filePath = $this->option('file');

        if (blank($backupId) && blank($filePath)) {
            $this->error('Specify either --backup-id=<ID> or --file=<path/to/backup.sql.gz>.');

            return self::INVALID;
        }

        if (filled($backupId) && filled($filePath)) {
            $this->error('Provide either --backup-id or --file, not both.');

            return self::INVALID;
        }

        $targetDescription = filled($backupId)
            ? "BackupLog #{$backupId} from Google Drive"
            : "Local file '{$filePath}'";

        if (! (bool) $this->option('force')) {
            $confirmed = $this->confirm(
                "WARNING: Restoring from {$targetDescription} will overwrite the live PostgreSQL database. Continue?",
                false
            );

            if (! $confirmed) {
                $this->warn('Database restore cancelled.');

                return self::INVALID;
            }
        }

        $createSafety = ! (bool) $this->option('skip-safety');

        $this->warn("Entering Maintenance Mode & preparing restore from {$targetDescription}...");

        try {
            if (filled($backupId)) {
                $backup = BackupLog::find((int) $backupId);
                if (! $backup) {
                    $this->error("BackupLog #{$backupId} was not found.");

                    return self::FAILURE;
                }

                $backupService->restoreFromBackupLog($backup, null, $createSafety);
            } else {
                $backupService->restoreFromLocalPath((string) $filePath, null, null, $createSafety);
            }

            $this->info('PostgreSQL database restored successfully and Maintenance Mode disabled.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('PostgreSQL restore failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
