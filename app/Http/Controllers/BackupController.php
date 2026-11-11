<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function __construct(protected DatabaseBackupService $backupService) {}

    /**
     * System Maintenance / Database Backup & Restore console.
     */
    public function index(): View
    {
        $backups = BackupLog::with('creator')
            ->latest()
            ->paginate(15);

        $latestSuccess = BackupLog::where('status', BackupLog::STATUS_SUCCESS)
            ->latest()
            ->first();

        $latestLog = BackupLog::latest()->first();
        $latestFailure = ($latestLog && $latestLog->status === BackupLog::STATUS_FAILED)
            ? $latestLog
            : null;

        $stats = [
            'total' => BackupLog::count(),
            'success' => BackupLog::where('status', BackupLog::STATUS_SUCCESS)->count(),
            'failed' => BackupLog::where('status', BackupLog::STATUS_FAILED)->count(),
            'automatic' => BackupLog::where('trigger_type', BackupLog::TRIGGER_AUTOMATIC)->count(),
        ];

        $diagnostics = $this->backupService->getDiagnostics();

        return view('admin.backups', compact(
            'backups',
            'latestSuccess',
            'latestFailure',
            'stats',
            'diagnostics'
        ));
    }

    /**
     * Trigger an immediate manual PostgreSQL backup & Google Drive upload.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $backup = $this->backupService->createBackup(
                BackupLog::TRIGGER_MANUAL,
                $request->user(),
                pruneExpired: true
            );

            $folderName = config('filesystems.disks.google.folder_name', 'Dormitory_system');

            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'success',
                    sprintf(
                        'Backup "%s" (%s) completed and uploaded to Google Drive folder "%s".',
                        $backup->file_name,
                        $backup->formatted_size,
                        $folderName
                    )
                );
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'error',
                    'Database backup failed: '.$e->getMessage().' Details have been recorded in Backup History and storage/logs/laravel.log.'
                );
        }
    }

    /**
     * Download a backup archive temporarily from Google Drive and remove temp file right after send.
     */
    public function download(BackupLog $backup): BinaryFileResponse|RedirectResponse
    {
        if (! $backup->isAvailableOnDrive()) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'This backup archive is not available for download from Google Drive.');
        }

        try {
            $tempPath = $this->backupService->downloadArchiveToTempPath($backup);

            return response()->download(
                $tempPath,
                basename($backup->file_name),
                ['Content-Type' => 'application/gzip']
            )->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Google Drive backup download failed: '.$e->getMessage(), [
                'backup_log_id' => $backup->id,
                'exception' => $e,
            ]);

            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Failed to download backup from Google Drive: '.$e->getMessage());
        }
    }

    /**
     * One-Click Restore from Google Drive backup history (protected by Admin password & Pre-Restore Safety).
     */
    public function restore(Request $request, BackupLog $backup): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check((string) $request->input('password'), (string) $request->user()->password)) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors(['password' => 'Incorrect Administrator password. Restore aborted.'])
                ->with('error', 'Restore aborted: Administrator password verification failed.');
        }

        if (! $backup->isAvailableOnDrive()) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Selected backup is unavailable on Google Drive and cannot be restored.');
        }

        try {
            $this->backupService->restoreFromBackupLog($backup, $request->user(), createSafetyBackup: true);

            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'success',
                    sprintf(
                        'Database successfully restored from "%s". A Pre-Restore Safety backup was saved prior to overwriting, and Maintenance Mode has been disabled.',
                        $backup->file_name
                    )
                );
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'error',
                    'Database restore failed: '.$e->getMessage().' Maintenance Mode has been automatically disabled and the error has been logged.'
                );
        }
    }

    /**
     * Manual .sql or .sql.gz file upload restore (protected by Admin password & Pre-Restore Safety).
     */
    public function uploadRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:512000'],
            'password' => ['required', 'string'],
        ]);

        $file = $request->file('backup_file');
        $originalName = strtolower((string) ($file?->getClientOriginalName() ?? ''));

        if (! str_ends_with($originalName, '.sql.gz') && ! str_ends_with($originalName, '.sql')) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors(['backup_file' => 'Invalid file format. Upload a .sql.gz or .sql backup file.'])
                ->with('error', 'Upload restore aborted: Only .sql.gz and .sql backup files are allowed.');
        }

        if (! Hash::check((string) $request->input('password'), (string) $request->user()->password)) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors(['password' => 'Incorrect Administrator password. Restore aborted.'])
                ->with('error', 'Upload restore aborted: Administrator password verification failed.');
        }

        try {
            $this->backupService->restoreFromUploadedFile($file, $request->user(), createSafetyBackup: true);

            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'success',
                    sprintf(
                        'Database successfully restored from uploaded file "%s". A Pre-Restore Safety backup was created automatically before restoring.',
                        $file->getClientOriginalName()
                    )
                );
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with(
                    'error',
                    'Upload restore failed: '.$e->getMessage().' Maintenance Mode has been automatically disabled and details were logged.'
                );
        }
    }
}
