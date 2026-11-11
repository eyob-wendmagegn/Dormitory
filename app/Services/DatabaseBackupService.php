<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DatabaseBackupService
{
    private const ALLOWED_TRIGGERS = [
        BackupLog::TRIGGER_AUTOMATIC,
        BackupLog::TRIGGER_MANUAL,
        BackupLog::TRIGGER_PRE_RESTORE,
    ];

    public function __construct(
        protected GoogleDriveBackupService $driveService,
        protected BackupProcessRunner $processRunner
    ) {}

    /**
     * Return configuration & preflight diagnostics for the Admin UI.
     *
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array
    {
        $backupConfig = config('filesystems.backup', []);
        $connectionName = (string) ($backupConfig['connection'] ?? 'pgsql');
        $dbConfig = config("database.connections.{$connectionName}", []);

        $pgDumpPath = $this->cleanConfiguredValue((string) ($backupConfig['pg_dump_path'] ?? 'pg_dump'));
        $psqlPath = $this->cleanConfiguredValue((string) ($backupConfig['psql_path'] ?? 'psql'));

        $pgDumpReady = $this->binarySeemsAvailable($pgDumpPath);
        $psqlReady = $this->binarySeemsAvailable($psqlPath);
        $driveStatus = $this->driveService->getConfigurationStatus();

        return [
            'connection' => $connectionName,
            'database' => (string) ($dbConfig['database'] ?? ''),
            'host' => (string) ($dbConfig['host'] ?? ''),
            'port' => (string) ($dbConfig['port'] ?? '5432'),
            'pg_dump_path' => $pgDumpPath,
            'psql_path' => $psqlPath,
            'pg_dump_ready' => $pgDumpReady,
            'psql_ready' => $psqlReady,
            'drive' => $driveStatus,
            'retention_days' => (int) ($backupConfig['retention_days'] ?? $driveStatus['retention_days'] ?? 30),
        ];
    }

    /**
     * Dump PostgreSQL database via pg_dump, compress as .sql.gz, upload to Google Drive,
     * enforce retention policy, and remove local temporary files.
     */
    public function createBackup(
        string $triggerType = BackupLog::TRIGGER_MANUAL,
        ?User $actor = null,
        bool $pruneExpired = true
    ): BackupLog {
        if (! in_array($triggerType, self::ALLOWED_TRIGGERS, true)) {
            $triggerType = BackupLog::TRIGGER_MANUAL;
        }

        $archiveName = $this->generateBackupFilename();
        $runDir = $this->createRunTempDirectory();
        $sqlPath = $runDir.DIRECTORY_SEPARATOR.'dump.sql';
        $gzPath = $runDir.DIRECTORY_SEPARATOR.$archiveName;
        $archiveSize = null;

        try {
            $db = $this->getPostgresConfig();
            $pgDump = $this->resolveBinaryPath('pg_dump_path', 'pg_dump');
            $timeout = (int) config('filesystems.backup.pg_dump_timeout', 600);

            $command = [
                $pgDump,
                '-h', $db['host'],
                '-p', $db['port'],
                '-U', $db['username'],
                '-F', 'p',
                '--no-owner',
                '--no-privileges',
                '--clean',
                '--if-exists',
                '-f', $sqlPath,
                $db['database'],
            ];

            $this->processRunner->run($command, ['PGPASSWORD' => $db['password']], $timeout);

            if (! is_file($sqlPath) || filesize($sqlPath) <= 0) {
                throw new RuntimeException('pg_dump completed without producing a non-empty SQL export file.');
            }

            $this->compressSqlToGzip($sqlPath, $gzPath);
            @unlink($sqlPath);

            $archiveSize = (int) filesize($gzPath);
            $uploaded = $this->driveService->uploadBackup($gzPath, $archiveName, 'application/gzip');

            $backupLog = BackupLog::create([
                'file_name' => $uploaded['name'] ?: $archiveName,
                'file_size' => $uploaded['size'] ?: $archiveSize,
                'google_drive_file_id' => $uploaded['id'],
                'trigger_type' => $triggerType,
                'status' => BackupLog::STATUS_SUCCESS,
                'created_by' => $actor?->id,
            ]);

            if (Schema::hasTable('audit_logs')) {
                AuditLog::log(
                    'database.backup.created',
                    sprintf('Created %s PostgreSQL backup %s (%s).', $triggerType, $backupLog->file_name, $backupLog->formatted_size),
                    [
                        'backup_log_id' => $backupLog->id,
                        'trigger_type' => $triggerType,
                        'google_drive_file_id' => $backupLog->google_drive_file_id,
                    ],
                    $actor
                );
            }

            if ($pruneExpired) {
                $this->pruneExpiredBackupsSafely();
            }

            return $backupLog;
        } catch (Throwable $e) {
            Log::error("PostgreSQL backup failed [{$triggerType}]: {$e->getMessage()}", [
                'file_name' => $archiveName,
                'actor_id' => $actor?->id,
                'exception' => $e,
            ]);

            BackupLog::create([
                'file_name' => $archiveName,
                'file_size' => $archiveSize,
                'google_drive_file_id' => null,
                'trigger_type' => $triggerType,
                'status' => BackupLog::STATUS_FAILED,
                'error_message' => Str::limit($e->getMessage(), 2000),
                'created_by' => $actor?->id,
            ]);

            throw $e;
        } finally {
            $this->cleanupDirectory($runDir);
        }
    }

    /**
     * Download a backup archive temporarily from Google Drive for HTTP streaming.
     */
    public function downloadArchiveToTempPath(BackupLog $backup): string
    {
        if (! $backup->isAvailableOnDrive()) {
            throw new RuntimeException('Selected backup is not available on Google Drive.');
        }

        $baseDir = $this->ensureBaseTempDirectory();
        $safeName = basename($backup->file_name);
        $tempPath = $baseDir.DIRECTORY_SEPARATOR.'download_'.Str::random(10).'_'.$safeName;

        return $this->driveService->downloadBackup((string) $backup->google_drive_file_id, $tempPath);
    }

    /**
     * Enforce the configured Google Drive retention policy (default 30 days).
     *
     * @return array{deleted_count: int, deleted_ids: list<string>, deleted_names: list<string>}
     */
    public function pruneExpiredBackups(?int $days = null): array
    {
        $retentionDays = max(1, $days ?? (int) config('filesystems.backup.retention_days', 30));
        $result = $this->driveService->pruneExpiredBackups($retentionDays);

        if (! empty($result['deleted_ids'])) {
            BackupLog::whereIn('google_drive_file_id', $result['deleted_ids'])
                ->update(['google_drive_file_id' => null]);

            Log::info('Pruned expired PostgreSQL backups from Google Drive.', [
                'retention_days' => $retentionDays,
                'deleted_count' => $result['deleted_count'],
                'deleted_files' => $result['deleted_names'],
            ]);
        }

        return $result;
    }

    /**
     * One-Click Restore from a historical BackupLog stored on Google Drive.
     */
    public function restoreFromBackupLog(
        BackupLog $backup,
        ?User $actor = null,
        bool $createSafetyBackup = true
    ): void {
        if (! $backup->isAvailableOnDrive()) {
            throw new RuntimeException('Only successful backups stored in Google Drive can be restored.');
        }

        $targetId = $backup->id;
        $targetFileName = $backup->file_name;
        $driveFileId = (string) $backup->google_drive_file_id;

        $this->executeProtectedRestore(
            targetLabel: $targetFileName,
            actor: $actor,
            createSafetyBackup: $createSafetyBackup,
            prepareSqlCallback: function (string $runDir, string $tempSqlPath) use ($driveFileId, $targetFileName): void {
                $tempGzPath = $runDir.DIRECTORY_SEPARATOR.basename($targetFileName);
                $this->driveService->downloadBackup($driveFileId, $tempGzPath);
                $this->decompressGzipToSql($tempGzPath, $tempSqlPath);
            },
            postRestoreCallback: function () use ($targetId): void {
                if (Schema::hasTable('backup_logs')) {
                    BackupLog::whereKey($targetId)->update(['restored_at' => now()]);
                }
            }
        );
    }

    /**
     * Restore from a manually uploaded .sql or .sql.gz file.
     */
    public function restoreFromUploadedFile(
        UploadedFile $uploadedFile,
        ?User $actor = null,
        bool $createSafetyBackup = true
    ): void {
        $originalName = $uploadedFile->getClientOriginalName();
        $realPath = $uploadedFile->getRealPath();

        if (! $realPath || ! is_file($realPath)) {
            throw new RuntimeException('Uploaded backup file could not be read from temporary storage.');
        }

        $this->restoreFromLocalPath($realPath, $originalName, $actor, $createSafetyBackup);
    }

    /**
     * Restore from any local .sql or .sql.gz file path (used by uploaded files and Artisan CLI).
     */
    public function restoreFromLocalPath(
        string $localFilePath,
        ?string $displayName = null,
        ?User $actor = null,
        bool $createSafetyBackup = true
    ): void {
        if (! is_file($localFilePath) || ! is_readable($localFilePath)) {
            throw new RuntimeException("Backup file does not exist or is unreadable: {$localFilePath}");
        }

        $fileName = $displayName ?: basename($localFilePath);
        $lowerName = strtolower($fileName);

        $isGzip = str_ends_with($lowerName, '.sql.gz') || str_ends_with($lowerName, '.gz');
        $isPlainSql = str_ends_with($lowerName, '.sql');

        if (! $isGzip && ! $isPlainSql) {
            throw new RuntimeException('Unsupported backup file format. Only .sql.gz and .sql files are accepted.');
        }

        $this->executeProtectedRestore(
            targetLabel: $fileName,
            actor: $actor,
            createSafetyBackup: $createSafetyBackup,
            prepareSqlCallback: function (string $runDir, string $tempSqlPath) use ($localFilePath, $isGzip): void {
                if ($isGzip) {
                    $this->decompressGzipToSql($localFilePath, $tempSqlPath);
                } else {
                    if (! @copy($localFilePath, $tempSqlPath)) {
                        throw new RuntimeException("Failed staging local SQL file for restore: {$localFilePath}");
                    }
                }
            }
        );
    }

    /**
     * Execute restore with safety guards:
     * 1) Pre-Restore Safety backup (while app is live; aborts before overwriting on failure)
     * 2) Maintenance Mode down()
     * 3) Decompress / Stage SQL + disconnect DB + run psql -v ON_ERROR_STOP=1
     * 4) Reconnect DB, preserve pre-restore log if overwritten by dump, audit log
     * 5) Cleanup temp files + guarantee Maintenance Mode up() in finally
     *
     * @param  callable(string, string): void  $prepareSqlCallback
     * @param  (callable(): void)|null  $postRestoreCallback
     */
    protected function executeProtectedRestore(
        string $targetLabel,
        ?User $actor,
        bool $createSafetyBackup,
        callable $prepareSqlCallback,
        ?callable $postRestoreCallback = null
    ): void {
        $safetySnapshot = null;

        if ($createSafetyBackup) {
            $safetyLog = $this->createBackup(BackupLog::TRIGGER_PRE_RESTORE, $actor, pruneExpired: false);
            $safetySnapshot = [
                'file_name' => $safetyLog->file_name,
                'file_size' => $safetyLog->file_size,
                'google_drive_file_id' => $safetyLog->google_drive_file_id,
                'trigger_type' => $safetyLog->trigger_type,
                'status' => $safetyLog->status,
                'created_by' => $safetyLog->created_by,
                'created_at' => $safetyLog->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'updated_at' => $safetyLog->updated_at?->toDateTimeString() ?? now()->toDateTimeString(),
            ];
        }

        $connectionName = (string) config('filesystems.backup.connection', 'pgsql');
        $runDir = $this->createRunTempDirectory();
        $tempSqlPath = $runDir.DIRECTORY_SEPARATOR.'restore.sql';

        Artisan::call('down', ['--retry' => 15]);

        try {
            $prepareSqlCallback($runDir, $tempSqlPath);

            if (! is_file($tempSqlPath) || filesize($tempSqlPath) <= 0) {
                throw new RuntimeException("Prepared SQL restore file is empty or missing for '{$targetLabel}'.");
            }

            $db = $this->getPostgresConfig();
            $psql = $this->resolveBinaryPath('psql_path', 'psql');
            $timeout = (int) config('filesystems.backup.psql_timeout', 1200);

            DB::disconnect($connectionName);

            $command = [
                $psql,
                '-h', $db['host'],
                '-p', $db['port'],
                '-U', $db['username'],
                '-d', $db['database'],
                '-v', 'ON_ERROR_STOP=1',
                '-f', $tempSqlPath,
            ];

            $this->processRunner->run($command, ['PGPASSWORD' => $db['password']], $timeout);

            DB::reconnect($connectionName);

            $this->ensureBackupLogsTableExists();
            $this->preserveSafetyBackupLog($safetySnapshot);

            if ($postRestoreCallback !== null) {
                $postRestoreCallback();
            }

            if (Schema::hasTable('audit_logs')) {
                AuditLog::log(
                    'database.restored',
                    sprintf('Restored PostgreSQL database from backup "%s".', $targetLabel),
                    [
                        'target_backup' => $targetLabel,
                        'pre_restore_safety_file' => $safetySnapshot['file_name'] ?? null,
                    ],
                    $actor
                );
            }

            Log::info("PostgreSQL database successfully restored from '{$targetLabel}'.", [
                'actor_id' => $actor?->id,
                'pre_restore_safety_file' => $safetySnapshot['file_name'] ?? null,
            ]);
        } catch (Throwable $e) {
            try {
                DB::reconnect($connectionName);
                $this->ensureBackupLogsTableExists();
                $this->preserveSafetyBackupLog($safetySnapshot);

                if (Schema::hasTable('backup_logs')) {
                    BackupLog::create([
                        'file_name' => $targetLabel,
                        'file_size' => null,
                        'google_drive_file_id' => null,
                        'trigger_type' => BackupLog::TRIGGER_MANUAL,
                        'status' => BackupLog::STATUS_FAILED,
                        'error_message' => Str::limit('[Restore Failed] '.$e->getMessage(), 2000),
                        'created_by' => $actor?->id,
                    ]);
                }
            } catch (Throwable $loggingException) {
                Log::warning('Failed writing restore failure entry to backup_logs: '.$loggingException->getMessage());
            }

            Log::error("PostgreSQL database restore failed for '{$targetLabel}': {$e->getMessage()}", [
                'actor_id' => $actor?->id,
                'exception' => $e,
            ]);

            throw $e;
        } finally {
            $this->cleanupDirectory($runDir);

            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
            }
        }
    }

    public function compressSqlToGzip(string $sqlPath, string $gzPath): void
    {
        $source = @fopen($sqlPath, 'rb');
        if ($source === false) {
            throw new RuntimeException("Unable to open SQL dump for compression: {$sqlPath}");
        }

        $gz = @gzopen($gzPath, 'wb9');
        if ($gz === false) {
            fclose($source);
            throw new RuntimeException("Unable to open gzip archive for writing: {$gzPath}");
        }

        try {
            while (! feof($source)) {
                $chunk = fread($source, 524288);
                if ($chunk === false) {
                    throw new RuntimeException("Failed reading SQL dump chunk during compression: {$sqlPath}");
                }
                if ($chunk !== '' && gzwrite($gz, $chunk) === 0) {
                    throw new RuntimeException("Failed writing compressed chunk to archive: {$gzPath}");
                }
            }
        } finally {
            fclose($source);
            gzclose($gz);
        }

        if (! is_file($gzPath) || filesize($gzPath) <= 0) {
            throw new RuntimeException("Compressed backup archive is empty: {$gzPath}");
        }
    }

    public function decompressGzipToSql(string $gzPath, string $sqlPath): void
    {
        $magicHandle = @fopen($gzPath, 'rb');
        $magic = $magicHandle ? fread($magicHandle, 2) : '';
        if ($magicHandle) {
            fclose($magicHandle);
        }

        if ($magic !== "\x1f\x8b") {
            throw new RuntimeException('Invalid .sql.gz archive: file does not have a valid gzip header.');
        }

        $gz = @gzopen($gzPath, 'rb');
        if ($gz === false) {
            throw new RuntimeException("Unable to open gzip archive for decompression: {$gzPath}");
        }

        $dest = @fopen($sqlPath, 'wb');
        if ($dest === false) {
            gzclose($gz);
            throw new RuntimeException("Unable to create decompressed SQL file: {$sqlPath}");
        }

        try {
            while (! gzeof($gz)) {
                $chunk = gzread($gz, 524288);
                if ($chunk === false) {
                    throw new RuntimeException("Corrupt gzip stream encountered while decompressing {$gzPath}");
                }
                if ($chunk !== '' && fwrite($dest, $chunk) === false) {
                    throw new RuntimeException("Failed writing decompressed SQL to {$sqlPath}");
                }
            }
        } finally {
            gzclose($gz);
            fclose($dest);
        }

        if (! is_file($sqlPath) || filesize($sqlPath) <= 0) {
            throw new RuntimeException('Decompressed SQL file is empty.');
        }
    }

    public function generateBackupFilename(): string
    {
        return 'dorm_backup_'.now()->format('Y-m-d_H-i').'.sql.gz';
    }

    public function cleanupDirectory(string $directory): void
    {
        if ($directory === '' || ! is_dir($directory)) {
            return;
        }

        $items = @scandir($directory);
        if (is_array($items)) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $fullPath = $directory.DIRECTORY_SEPARATOR.$item;
                if (is_dir($fullPath)) {
                    $this->cleanupDirectory($fullPath);
                } else {
                    @unlink($fullPath);
                }
            }
        }

        @rmdir($directory);
    }

    protected function pruneExpiredBackupsSafely(): void
    {
        try {
            $this->pruneExpiredBackups();
        } catch (Throwable $e) {
            Log::warning('Backup uploaded successfully, but 30-day Google Drive retention pruning encountered an error: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    protected function ensureBackupLogsTableExists(): void
    {
        if (! Schema::hasTable('backup_logs')) {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2026_11_10_000001_create_backup_logs_table.php',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $safetySnapshot
     */
    protected function preserveSafetyBackupLog(?array $safetySnapshot): void
    {
        if (! $safetySnapshot || empty($safetySnapshot['google_drive_file_id']) || ! Schema::hasTable('backup_logs')) {
            return;
        }

        $exists = BackupLog::where('google_drive_file_id', $safetySnapshot['google_drive_file_id'])->exists();
        if (! $exists) {
            $actorId = $safetySnapshot['created_by'];
            if ($actorId && ! User::whereKey($actorId)->exists()) {
                $safetySnapshot['created_by'] = null;
            }
            BackupLog::create($safetySnapshot);
        }
    }

    /**
     * @return array{host: string, port: string, database: string, username: string, password: string}
     */
    protected function getPostgresConfig(): array
    {
        $connectionName = (string) config('filesystems.backup.connection', 'pgsql');
        $config = config("database.connections.{$connectionName}");

        if (! is_array($config) || ($config['driver'] ?? null) !== 'pgsql') {
            throw new RuntimeException("PostgreSQL database connection '{$connectionName}' is not properly configured.");
        }

        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');

        if ($database === '' || $username === '') {
            throw new RuntimeException("Database name and username must be configured for connection '{$connectionName}'.");
        }

        return [
            'host' => (string) ($config['host'] ?? '127.0.0.1'),
            'port' => (string) ($config['port'] ?? '5432'),
            'database' => $database,
            'username' => $username,
            'password' => (string) ($config['password'] ?? ''),
        ];
    }

    protected function resolveBinaryPath(string $configKey, string $default): string
    {
        $raw = (string) config("filesystems.backup.{$configKey}", $default);
        $cleaned = $this->cleanConfiguredValue($raw);

        return $cleaned !== '' ? $cleaned : $default;
    }

    protected function cleanConfiguredValue(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    protected function binarySeemsAvailable(string $binaryPath): bool
    {
        if ($binaryPath === '') {
            return false;
        }

        if (str_contains($binaryPath, '\\') || str_contains($binaryPath, '/')) {
            return is_file($binaryPath);
        }

        return true;
    }

    protected function ensureBaseTempDirectory(): string
    {
        $baseDir = $this->cleanConfiguredValue((string) config('filesystems.backup.temp_path', storage_path('app/backups')));
        if (! is_dir($baseDir) && ! mkdir($baseDir, 0755, true) && ! is_dir($baseDir)) {
            throw new RuntimeException("Unable to create backup temporary directory: {$baseDir}");
        }

        return $baseDir;
    }

    protected function createRunTempDirectory(): string
    {
        $baseDir = $this->ensureBaseTempDirectory();
        $runDir = $baseDir.DIRECTORY_SEPARATOR.'run_'.Str::random(12);

        if (! mkdir($runDir, 0755, true) && ! is_dir($runDir)) {
            throw new RuntimeException("Unable to create isolated temporary run directory: {$runDir}");
        }

        return $runDir;
    }
}
