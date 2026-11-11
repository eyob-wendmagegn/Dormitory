<?php

namespace Tests\Feature;

use App\Models\BackupLog;
use App\Models\User;
use App\Services\BackupProcessRunner;
use App\Services\GoogleDriveBackupService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DatabaseBackupAndRestoreTest extends TestCase
{
    use RefreshDatabase;

    private string $tempBackupRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempBackupRoot = storage_path('app/testing-backups-'.uniqid());

        config([
            'filesystems.backup.temp_path' => $this->tempBackupRoot,
            'filesystems.backup.connection' => 'pgsql',
            'filesystems.backup.pg_dump_path' => '/usr/bin/pg_dump',
            'filesystems.backup.psql_path' => '/usr/bin/psql',
            'filesystems.backup.retention_days' => 30,
            'database.connections.pgsql.host' => '127.0.0.1',
            'database.connections.pgsql.port' => '5432',
            'database.connections.pgsql.database' => 'dorm_db',
            'database.connections.pgsql.username' => 'postgres',
            'database.connections.pgsql.password' => 'super-secret-pg-pass',
            'filesystems.disks.google.client_id' => '',
            'filesystems.disks.google.client_secret' => '',
            'filesystems.disks.google.refresh_token' => '',
            'filesystems.disks.google.credentials_path' => $this->tempBackupRoot.'/missing-service-account.json',
            'filesystems.disks.google.folder_name' => 'Dormitory_system',
            'filesystems.disks.google.folder_id' => 'drive-folder-dorm-123',
        ]);
    }

    protected function tearDown(): void
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }

        File::deleteDirectory($this->tempBackupRoot);

        parent::tearDown();
    }

    protected function makeUser(string $role, string $password = 'admin-pass-123'): User
    {
        static $counter = 1;
        $idx = $counter++;

        return User::create([
            'name' => ucfirst($role).' User '.$idx,
            'university_id' => 'U-BACK-'.$idx,
            'email' => $role.$idx.'@woldia.edu.et',
            'password' => Hash::make($password),
            'role' => $role,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);
    }

    public function test_only_system_admin_can_access_backup_and_restore_routes(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        $proctor = $this->makeUser(User::ROLE_PROCTOR);
        $manager = $this->makeUser(User::ROLE_PROCTOR_MANAGER);
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $backup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-10_02-00.sql.gz',
            'file_size' => 1024,
            'google_drive_file_id' => 'drive-1',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        // Guest redirected to login
        $this->get(route('admin.backups.index'))->assertRedirect('/login');
        $this->post(route('admin.backups.store'))->assertRedirect('/login');

        // Non-admin roles blocked
        foreach ([$student, $proctor, $manager] as $nonAdmin) {
            $this->actingAs($nonAdmin)->get(route('admin.backups.index'))->assertRedirect()->assertSessionHas('error');
            $this->actingAs($nonAdmin)->post(route('admin.backups.store'))->assertRedirect()->assertSessionHas('error');
            $this->actingAs($nonAdmin)->get(route('admin.backups.download', $backup))->assertRedirect()->assertSessionHas('error');
            $this->actingAs($nonAdmin)->post(route('admin.backups.restore', $backup), ['password' => 'anything'])->assertRedirect()->assertSessionHas('error');
            $this->actingAs($nonAdmin)->post(route('admin.backups.upload_restore'), ['password' => 'anything'])->assertRedirect()->assertSessionHas('error');
        }

        // Admin allowed
        $response = $this->actingAs($admin)->get(route('admin.backups.index'));
        $response->assertOk();
        $response->assertSee('PostgreSQL Database Backup & Restore', false);
        $response->assertSee('Backup Now', false);
        $response->assertSee('Manual Upload & Restore', false);
        $response->assertSee('Backup History & Cloud Archives', false);
        $response->assertSee('dorm_backup_2026-11-10_02-00.sql.gz', false);
    }

    public function test_manual_backup_runs_pg_dump_compresses_to_sql_gz_uploads_to_google_drive_and_cleans_temp_files(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $capturedSqlContent = null;
        $capturedFilename = null;

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->withArgs(function (array $command, array $env): bool {
                $this->assertSame('/usr/bin/pg_dump', $command[0]);
                $this->assertContains('--no-owner', $command);
                $this->assertContains('--clean', $command);
                $this->assertContains('dorm_db', $command);
                $this->assertSame('super-secret-pg-pass', $env['PGPASSWORD'] ?? null);

                $fileFlagIndex = array_search('-f', $command, true);
                $this->assertNotFalse($fileFlagIndex);
                $sqlPath = $command[$fileFlagIndex + 1];

                file_put_contents($sqlPath, "-- PostgreSQL database dump\nCREATE TABLE test_table (id INT);\nINSERT INTO test_table VALUES (42);\n");

                return true;
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('getConfigurationStatus')->andReturn([
            'configured' => true,
            'auth_mode' => 'oauth2',
            'oauth_configured' => true,
            'credentials_path' => 'storage/app/google/service-account.json',
            'credentials_exists' => false,
            'credentials_valid' => false,
            'service_account_email' => null,
            'folder_id' => 'drive-folder-dorm-123',
            'folder_name' => 'Dormitory_system',
            'retention_days' => 30,
            'issues' => [],
        ]);
        $drive->shouldReceive('uploadBackup')
            ->once()
            ->withArgs(function (string $localGzPath, string $fileName, string $mimeType) use (&$capturedSqlContent, &$capturedFilename): bool {
                $this->assertFileExists($localGzPath);
                $this->assertMatchesRegularExpression('/^dorm_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.sql\.gz$/', $fileName);
                $this->assertSame('application/gzip', $mimeType);

                $rawBytes = file_get_contents($localGzPath);
                $this->assertStringStartsWith("\x1f\x8b", $rawBytes);

                $capturedSqlContent = gzdecode($rawBytes);
                $capturedFilename = $fileName;

                return true;
            })
            ->andReturnUsing(fn (string $localGzPath, string $fileName) => [
                'id' => 'gdrive-uploaded-999',
                'name' => $fileName,
                'size' => filesize($localGzPath),
            ]);
        $drive->shouldReceive('pruneExpiredBackups')
            ->once()
            ->with(30)
            ->andReturn(['deleted_count' => 0, 'deleted_ids' => [], 'deleted_names' => []]);

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $response = $this->actingAs($admin)->post(route('admin.backups.store'));

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('success');

        $this->assertStringContainsString('INSERT INTO test_table VALUES (42);', (string) $capturedSqlContent);
        $this->assertDatabaseHas('backup_logs', [
            'file_name' => $capturedFilename,
            'google_drive_file_id' => 'gdrive-uploaded-999',
            'trigger_type' => BackupLog::TRIGGER_MANUAL,
            'status' => BackupLog::STATUS_SUCCESS,
            'created_by' => $admin->id,
        ]);

        // Temp directory must not contain leftover .sql or .sql.gz dumps
        $remainingFiles = is_dir($this->tempBackupRoot) ? File::allFiles($this->tempBackupRoot) : [];
        $this->assertCount(0, $remainingFiles);
    }

    public function test_failed_backup_logs_error_and_displays_warning_banner_on_admin_dashboard(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andThrow(new RuntimeException('pg_dump connection refused on port 5432'));

        $this->app->instance(BackupProcessRunner::class, $runner);

        $response = $this->actingAs($admin)->post(route('admin.backups.store'));

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('backup_logs', [
            'trigger_type' => BackupLog::TRIGGER_MANUAL,
            'status' => BackupLog::STATUS_FAILED,
            'created_by' => $admin->id,
        ]);

        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('PostgreSQL Backup / Restore Warning', false);
        $dashboardResponse->assertSee('pg_dump connection refused on port 5432', false);

        $remainingFiles = is_dir($this->tempBackupRoot) ? File::allFiles($this->tempBackupRoot) : [];
        $this->assertCount(0, $remainingFiles);
    }

    public function test_history_restore_rejects_wrong_admin_password(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, 'CorrectAdminPass!');

        $targetBackup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-09_02-00.sql.gz',
            'file_size' => 512,
            'google_drive_file_id' => 'gdrive-target-555',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldNotReceive('run');
        $this->app->instance(BackupProcessRunner::class, $runner);

        $badResponse = $this->actingAs($admin)->post(route('admin.backups.restore', $targetBackup), [
            'password' => 'wrong-password',
        ]);

        $badResponse->assertRedirect(route('admin.backups.index'));
        $badResponse->assertSessionHasErrors('password');
        $badResponse->assertSessionHas('error');
        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_history_restore_takes_pre_restore_safety_backup_enters_maintenance_mode_and_runs_psql(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, 'CorrectAdminPass!');

        $targetBackup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-09_02-00.sql.gz',
            'file_size' => 512,
            'google_drive_file_id' => 'gdrive-target-555',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $executedSteps = [];
        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (array $command, array $env) use (&$executedSteps): string {
                if ($command[0] === '/usr/bin/pg_dump') {
                    $executedSteps[] = 'pg_dump';
                    $idx = array_search('-f', $command, true);
                    file_put_contents($command[$idx + 1], "-- Pre-restore safety SQL\nSELECT 1;\n");
                } elseif ($command[0] === '/usr/bin/psql') {
                    $executedSteps[] = 'psql';
                    $this->assertTrue(app()->isDownForMaintenance(), 'App must be in Maintenance Mode during psql execution');
                    $idx = array_search('-f', $command, true);
                    $restoredSql = file_get_contents($command[$idx + 1]);
                    $this->assertStringContainsString('RESTORE TARGET SQL', (string) $restoredSql);
                    $this->assertSame('super-secret-pg-pass', $env['PGPASSWORD'] ?? null);
                }

                return '';
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('uploadBackup')
            ->once()
            ->andReturn([
                'id' => 'gdrive-safety-777',
                'name' => 'dorm_backup_safety.sql.gz',
                'size' => 128,
            ]);
        $drive->shouldReceive('downloadBackup')
            ->once()
            ->with('gdrive-target-555', Mockery::type('string'))
            ->andReturnUsing(function (string $driveId, string $destPath): string {
                file_put_contents($destPath, gzencode("-- RESTORE TARGET SQL\nSELECT 2;\n"));

                return $destPath;
            });

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $goodResponse = $this->actingAs($admin)->post(route('admin.backups.restore', $targetBackup), [
            'password' => 'CorrectAdminPass!',
        ]);

        $goodResponse->assertRedirect(route('admin.backups.index'));
        $goodResponse->assertSessionHas('success');
        $this->assertSame(['pg_dump', 'psql'], $executedSteps);
        $this->assertFalse(app()->isDownForMaintenance(), 'App must exit Maintenance Mode after restore');

        $this->assertDatabaseHas('backup_logs', [
            'google_drive_file_id' => 'gdrive-safety-777',
            'trigger_type' => BackupLog::TRIGGER_PRE_RESTORE,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);
        $this->assertNotNull($targetBackup->fresh()->restored_at);

        $remainingFiles = is_dir($this->tempBackupRoot) ? File::allFiles($this->tempBackupRoot) : [];
        $this->assertCount(0, $remainingFiles);
    }

    public function test_restore_exits_maintenance_mode_even_when_psql_fails(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, 'AdminSecret!');

        $targetBackup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-08_02-00.sql.gz',
            'file_size' => 512,
            'google_drive_file_id' => 'gdrive-broken-111',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (array $command): string {
                if ($command[0] === '/usr/bin/pg_dump') {
                    $idx = array_search('-f', $command, true);
                    file_put_contents($command[$idx + 1], "-- Safety dump\nSELECT 1;\n");

                    return '';
                }

                $this->assertTrue(app()->isDownForMaintenance());
                throw new RuntimeException('psql syntax error at line 42');
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('uploadBackup')
            ->once()
            ->andReturn(['id' => 'gdrive-safety-111', 'name' => 'safety.sql.gz', 'size' => 100]);
        $drive->shouldReceive('downloadBackup')
            ->once()
            ->andReturnUsing(function (string $driveId, string $destPath): string {
                file_put_contents($destPath, gzencode('INVALID SQL STATEMENT;'));

                return $destPath;
            });

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $response = $this->actingAs($admin)->post(route('admin.backups.restore', $targetBackup), [
            'password' => 'AdminSecret!',
        ]);

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('error');
        $this->assertFalse(app()->isDownForMaintenance(), 'Maintenance Mode MUST be turned off if restore fails');
        $this->assertDatabaseHas('backup_logs', [
            'file_name' => 'dorm_backup_2026-11-08_02-00.sql.gz',
            'status' => BackupLog::STATUS_FAILED,
        ]);

        $remainingFiles = is_dir($this->tempBackupRoot) ? File::allFiles($this->tempBackupRoot) : [];
        $this->assertCount(0, $remainingFiles);
    }

    public function test_upload_and_restore_rejects_invalid_extensions(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, 'UploadPass!');

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldNotReceive('run');
        $this->app->instance(BackupProcessRunner::class, $runner);

        $invalidFile = UploadedFile::fake()->createWithContent('notes.txt', 'hello world');
        $invalidResp = $this->actingAs($admin)->post(route('admin.backups.upload_restore'), [
            'backup_file' => $invalidFile,
            'password' => 'UploadPass!',
        ]);

        $invalidResp->assertSessionHasErrors('backup_file');
        $invalidResp->assertSessionHas('error');
    }

    public function test_upload_and_restore_supports_sql_gz_and_plain_sql_with_safety_backup(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN, 'UploadPass!');

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->times(4)
            ->andReturnUsing(function (array $command): string {
                if ($command[0] === '/usr/bin/pg_dump') {
                    $idx = array_search('-f', $command, true);
                    file_put_contents($command[$idx + 1], "-- Safety before upload restore\n");
                } elseif ($command[0] === '/usr/bin/psql') {
                    $idx = array_search('-f', $command, true);
                    $content = (string) file_get_contents($command[$idx + 1]);
                    $this->assertTrue(
                        str_contains($content, 'MANUAL UPLOAD GZIP SQL') || str_contains($content, 'MANUAL UPLOAD PLAIN SQL')
                    );
                }

                return '';
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('uploadBackup')
            ->twice()
            ->andReturn(['id' => 'gdrive-safety-upload-1', 'name' => 'safety.sql.gz', 'size' => 90]);

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $gzFile = UploadedFile::fake()->createWithContent(
            'manual_snapshot.sql.gz',
            (string) gzencode("-- MANUAL UPLOAD GZIP SQL\nCREATE TABLE dummy (x INT);\n")
        );

        $gzResponse = $this->actingAs($admin)->post(route('admin.backups.upload_restore'), [
            'backup_file' => $gzFile,
            'password' => 'UploadPass!',
        ]);
        $gzResponse->assertRedirect(route('admin.backups.index'));
        $gzResponse->assertSessionHas('success');

        $sqlFile = UploadedFile::fake()->createWithContent(
            'manual_snapshot.sql',
            "-- MANUAL UPLOAD PLAIN SQL\nCREATE TABLE dummy2 (x INT);\n"
        );

        $sqlResponse = $this->actingAs($admin)->post(route('admin.backups.upload_restore'), [
            'backup_file' => $sqlFile,
            'password' => 'UploadPass!',
        ]);
        $sqlResponse->assertRedirect(route('admin.backups.index'));
        $sqlResponse->assertSessionHas('success');
        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_admin_can_download_backup_archive_from_google_drive(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $backup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-10_02-00.sql.gz',
            'file_size' => 128,
            'google_drive_file_id' => 'gdrive-dl-321',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $gzBytes = (string) gzencode("-- Downloadable dump\nSELECT 1;\n");

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('downloadBackup')
            ->once()
            ->with('gdrive-dl-321', Mockery::type('string'))
            ->andReturnUsing(function (string $driveId, string $destPath) use ($gzBytes): string {
                file_put_contents($destPath, $gzBytes);

                return $destPath;
            });

        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $response = $this->actingAs($admin)->get(route('admin.backups.download', $backup));
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=dorm_backup_2026-11-10_02-00.sql.gz');
    }

    public function test_artisan_backup_and_restore_commands_and_2am_schedule_work(): void
    {
        $expiredLog = BackupLog::create([
            'file_name' => 'dorm_backup_2026-09-01_02-00.sql.gz',
            'file_size' => 1024,
            'google_drive_file_id' => 'old-drive-file-42',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (array $command): string {
                $idx = array_search('-f', $command, true);
                file_put_contents($command[$idx + 1], "-- Nightly 2 AM Backup\n");

                return '';
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('uploadBackup')
            ->once()
            ->andReturn(['id' => 'new-drive-file-99', 'name' => 'dorm_backup_now.sql.gz', 'size' => 256]);
        $drive->shouldReceive('pruneExpiredBackups')
            ->once()
            ->with(30)
            ->andReturn([
                'deleted_count' => 1,
                'deleted_ids' => ['old-drive-file-42'],
                'deleted_names' => ['dorm_backup_2026-09-01_02-00.sql.gz'],
            ]);

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $this->artisan('db:backup', ['--type' => BackupLog::TRIGGER_AUTOMATIC])
            ->assertExitCode(0);

        // Pruned file's google_drive_file_id must be cleared while keeping audit history row
        $this->assertNull($expiredLog->fresh()->google_drive_file_id);
        $this->assertDatabaseHas('backup_logs', [
            'google_drive_file_id' => 'new-drive-file-99',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        // Verify 2:00 AM daily cron expression in Laravel Schedule
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());
        $backupEvent = $events->first(fn ($event) => str_contains((string) $event->command, 'db:backup'));

        $this->assertNotNull($backupEvent, 'db:backup command must be scheduled in routes/console.php');
        $this->assertSame('0 2 * * *', $backupEvent->expression);
    }

    public function test_artisan_restore_command_downloads_and_executes_psql(): void
    {
        $targetBackup = BackupLog::create([
            'file_name' => 'dorm_backup_2026-11-10_02-00.sql.gz',
            'file_size' => 256,
            'google_drive_file_id' => 'cli-restore-77',
            'trigger_type' => BackupLog::TRIGGER_AUTOMATIC,
            'status' => BackupLog::STATUS_SUCCESS,
        ]);

        $runner = Mockery::mock(BackupProcessRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->withArgs(function (array $command): bool {
                return $command[0] === '/usr/bin/psql' && in_array('ON_ERROR_STOP=1', $command, true);
            });

        $drive = Mockery::mock(GoogleDriveBackupService::class);
        $drive->shouldReceive('downloadBackup')
            ->once()
            ->with('cli-restore-77', Mockery::type('string'))
            ->andReturnUsing(function (string $driveId, string $destPath): string {
                file_put_contents($destPath, gzencode("-- CLI Restore SQL\nSELECT 1;\n"));

                return $destPath;
            });

        $this->app->instance(BackupProcessRunner::class, $runner);
        $this->app->instance(GoogleDriveBackupService::class, $drive);

        $this->artisan('db:restore', [
            '--backup-id' => $targetBackup->id,
            '--skip-safety' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_backup_process_runner_preserves_essential_platform_env_vars(): void
    {
        $runner = new BackupProcessRunner();
        $resolved = $runner->mergeEssentialPlatformEnv(['PGPASSWORD' => 'secret123']);

        $this->assertSame('secret123', $resolved['PGPASSWORD']);

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->assertArrayHasKey('SystemRoot', $resolved);
            $this->assertNotEmpty($resolved['SystemRoot']);
            $this->assertArrayHasKey('TEMP', $resolved);
            $this->assertNotEmpty($resolved['TEMP']);
        }
    }

    public function test_google_drive_service_reports_oauth2_status_and_admin_ui_renders_oauth_badge(): void
    {
        File::makeDirectory($this->tempBackupRoot, 0755, true, true);
        $fakePgDump = $this->tempBackupRoot.DIRECTORY_SEPARATOR.'pg_dump';
        $fakePsql = $this->tempBackupRoot.DIRECTORY_SEPARATOR.'psql';
        file_put_contents($fakePgDump, 'stub');
        file_put_contents($fakePsql, 'stub');

        config([
            'filesystems.backup.pg_dump_path' => $fakePgDump,
            'filesystems.backup.psql_path' => $fakePsql,
            'filesystems.disks.google.client_id' => '736228513545-test.apps.googleusercontent.com',
            'filesystems.disks.google.client_secret' => 'GOCSPX-test-secret',
            'filesystems.disks.google.refresh_token' => '1//test-refresh-token',
            'filesystems.disks.google.credentials_path' => $this->tempBackupRoot.DIRECTORY_SEPARATOR.'nonexistent.json',
            'filesystems.disks.google.folder_id' => 'oauth-folder-xyz',
        ]);

        $status = app(GoogleDriveBackupService::class)->getConfigurationStatus();

        $this->assertTrue($status['configured']);
        $this->assertSame('oauth2', $status['auth_mode']);
        $this->assertTrue($status['oauth_configured']);
        $this->assertSame([], $status['issues']);

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $response = $this->actingAs($admin)->get(route('admin.backups.index'));

        $response->assertOk();
        $response->assertSee('OAuth2 Connected', false);
        $response->assertSee('Folder ID: oauth-folder-xyz', false);
        $response->assertDontSee('Environment & Google Drive Configuration Checklist', false);
    }

    public function test_google_drive_oauth2_folder_resolution_auto_creates_target_folder_when_not_yet_visible(): void
    {
        $oauthDriveService = new GoogleDriveBackupService([
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'refresh-token',
            'folder_id' => '',
            'folder_name' => 'Dormitory_system',
        ]);

        $filesResource = Mockery::mock();
        $emptyList = new \Google\Service\Drive\FileList();
        $emptyList->setFiles([]);
        $createdFolder = new \Google\Service\Drive\DriveFile();
        $createdFolder->setId('created-oauth-folder-456');
        $createdFolder->setName('Dormitory_system');

        $filesResource->shouldReceive('listFiles')->once()->andReturn($emptyList);
        $filesResource->shouldReceive('create')
            ->once()
            ->withArgs(function (\Google\Service\Drive\DriveFile $metadata): bool {
                return $metadata->getName() === 'Dormitory_system'
                    && $metadata->getMimeType() === 'application/vnd.google-apps.folder';
            })
            ->andReturn($createdFolder);

        $mockDrive = Mockery::mock(\Google\Service\Drive::class);
        $mockDrive->files = $filesResource;

        $method = new \ReflectionMethod(GoogleDriveBackupService::class, 'resolveTargetFolderId');
        $method->setAccessible(true);

        $this->assertSame('created-oauth-folder-456', $method->invoke($oauthDriveService, $mockDrive));

        // Service-account mode still throws if folder is not visible / shared
        $serviceAccountDriveService = new GoogleDriveBackupService([
            'client_id' => '',
            'client_secret' => '',
            'refresh_token' => '',
            'folder_id' => '',
            'folder_name' => 'Dormitory_system',
        ]);

        $saFilesResource = Mockery::mock();
        $saFilesResource->shouldReceive('listFiles')->once()->andReturn($emptyList);
        $saMockDrive = Mockery::mock(\Google\Service\Drive::class);
        $saMockDrive->files = $saFilesResource;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Target Google Drive folder 'Dormitory_system' could not be found");
        $method->invoke($serviceAccountDriveService, $saMockDrive);
    }
}
