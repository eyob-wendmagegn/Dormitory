<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Google\Client as GoogleClient;
use Google\Http\MediaFileUpload;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class GoogleDriveBackupService
{
    private const RESUMABLE_THRESHOLD_BYTES = 4 * 1024 * 1024; // 4 MB
    private const CHUNK_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB (multiple of 256 KB)

    /** @var array<string, mixed> */
    protected array $config;

    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config('filesystems.disks.google', []);
    }

    /**
     * Return non-destructive preflight readiness information for the Admin UI.
     *
     * @return array{
     *     configured: bool,
     *     auth_mode: string,
     *     oauth_configured: bool,
     *     credentials_path: string,
     *     credentials_exists: bool,
     *     credentials_valid: bool,
     *     service_account_email: ?string,
     *     folder_id: ?string,
     *     folder_name: string,
     *     retention_days: int,
     *     issues: list<string>
     * }
     */
    public function getConfigurationStatus(): array
    {
        $clientId = trim((string) ($this->config['client_id'] ?? ''));
        $clientSecret = trim((string) ($this->config['client_secret'] ?? ''));
        $refreshToken = trim((string) ($this->config['refresh_token'] ?? ''));
        $oauthConfigured = $clientId !== '' && $clientSecret !== '' && $refreshToken !== '';

        $rawPath = (string) ($this->config['credentials_path'] ?? storage_path('app/google/service-account.json'));
        $resolvedPath = $this->resolvePath($rawPath);
        $credentialsExists = $resolvedPath !== '' && is_file($resolvedPath);
        $credentialsValid = false;
        $serviceAccountEmail = null;
        $issues = [];

        if (! $oauthConfigured) {
            if (! $credentialsExists) {
                $issues[] = 'Configure Google Drive OAuth2 credentials (GOOGLE_DRIVE_CLIENT_ID / CLIENT_SECRET / REFRESH_TOKEN) in .env or provide a Service Account JSON key at: '.$resolvedPath;
            } else {
                $rawJson = @file_get_contents($resolvedPath);
                $decoded = is_string($rawJson) ? json_decode($rawJson, true) : null;

                if (! is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
                    $issues[] = 'Service Account JSON key is invalid or missing required fields (client_email / private_key).';
                } else {
                    $credentialsValid = true;
                    $serviceAccountEmail = (string) $decoded['client_email'];
                }
            }
        }

        $folderId = trim((string) ($this->config['folder_id'] ?? '')) ?: null;
        $folderName = trim((string) ($this->config['folder_name'] ?? 'Dormitory_system')) ?: 'Dormitory_system';

        $authReady = $oauthConfigured || $credentialsValid;
        $authMode = $oauthConfigured ? 'oauth2' : ($credentialsValid ? 'service_account' : 'none');

        return [
            'configured' => $authReady && ($folderId !== null || $folderName !== ''),
            'auth_mode' => $authMode,
            'oauth_configured' => $oauthConfigured,
            'credentials_path' => $resolvedPath,
            'credentials_exists' => $credentialsExists,
            'credentials_valid' => $credentialsValid,
            'service_account_email' => $serviceAccountEmail,
            'folder_id' => $folderId,
            'folder_name' => $folderName,
            'retention_days' => max(1, (int) ($this->config['retention_days'] ?? 30)),
            'issues' => $issues,
        ];
    }

    /**
     * Upload a compressed .sql.gz backup file into the configured Google Drive folder.
     *
     * @return array{id: string, name: string, size: int}
     */
    public function uploadBackup(string $localFilePath, string $targetFileName, string $mimeType = 'application/gzip'): array
    {
        if (! is_file($localFilePath) || ! is_readable($localFilePath)) {
            throw new RuntimeException("Local backup file does not exist or is unreadable: {$localFilePath}");
        }

        $fileSize = (int) filesize($localFilePath);
        if ($fileSize <= 0) {
            throw new RuntimeException("Cannot upload empty backup archive: {$localFilePath}");
        }

        $client = $this->createAuthenticatedClient();
        $drive = new GoogleDrive($client);
        $folderId = $this->resolveTargetFolderId($drive);

        $fileMetadata = new DriveFile([
            'name' => $targetFileName,
            'parents' => [$folderId],
            'description' => sprintf(
                'PostgreSQL backup for %s created on %s UTC',
                config('app.name', 'Dormitory Management System'),
                now()->utc()->toDateTimeString()
            ),
        ]);

        if ($fileSize <= self::RESUMABLE_THRESHOLD_BYTES) {
            $content = file_get_contents($localFilePath);
            if ($content === false) {
                throw new RuntimeException("Failed to read backup archive for upload: {$localFilePath}");
            }

            $createdFile = $drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'supportsAllDrives' => true,
                'fields' => 'id, name, size, createdTime',
            ]);
        } else {
            $createdFile = $this->uploadViaResumableChunks($client, $drive, $fileMetadata, $localFilePath, $fileSize, $mimeType);
        }

        if (! $createdFile instanceof DriveFile || empty($createdFile->getId())) {
            throw new RuntimeException('Google Drive upload completed without returning a valid file ID.');
        }

        return [
            'id' => (string) $createdFile->getId(),
            'name' => (string) ($createdFile->getName() ?: $targetFileName),
            'size' => (int) ($createdFile->getSize() ?: $fileSize),
        ];
    }

    /**
     * Download a backup archive from Google Drive to a local temporary path.
     */
    public function downloadBackup(string $driveFileId, string $destinationPath): string
    {
        if (trim($driveFileId) === '') {
            throw new RuntimeException('Missing Google Drive file ID for download.');
        }

        $dir = dirname($destinationPath);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException("Unable to create temporary download directory: {$dir}");
        }

        $client = $this->createAuthenticatedClient();
        $drive = new GoogleDrive($client);

        try {
            $response = $drive->files->get($driveFileId, [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);

            $body = $response->getBody();
            $handle = fopen($destinationPath, 'wb');
            if ($handle === false) {
                throw new RuntimeException("Unable to open local destination file for writing: {$destinationPath}");
            }

            try {
                while (! $body->eof()) {
                    $chunk = $body->read(524288);
                    if ($chunk === '') {
                        continue;
                    }
                    if (fwrite($handle, $chunk) === false) {
                        throw new RuntimeException("Failed writing downloaded backup chunk to {$destinationPath}");
                    }
                }
            } finally {
                fclose($handle);
            }

            if (! is_file($destinationPath) || filesize($destinationPath) <= 0) {
                throw new RuntimeException("Downloaded backup file is empty or missing: {$destinationPath}");
            }

            return $destinationPath;
        } catch (\Throwable $e) {
            if (is_file($destinationPath)) {
                @unlink($destinationPath);
            }
            throw $e;
        }
    }

    /**
     * Delete backup files in the target Google Drive folder older than $retentionDays days.
     *
     * @return array{deleted_count: int, deleted_ids: list<string>, deleted_names: list<string>}
     */
    public function pruneExpiredBackups(?int $retentionDays = null, ?CarbonInterface $now = null): array
    {
        $days = max(1, $retentionDays ?? (int) ($this->config['retention_days'] ?? 30));
        $cutoff = ($now ? $now->copy() : now())->utc()->subDays($days);

        $client = $this->createAuthenticatedClient();
        $drive = new GoogleDrive($client);
        $folderId = $this->resolveTargetFolderId($drive);

        $query = sprintf(
            "'%s' in parents and trashed = false and mimeType != 'application/vnd.google-apps.folder' and createdTime < '%s'",
            $this->escapeDriveQueryLiteral($folderId),
            $cutoff->toIso8601String()
        );

        $deletedIds = [];
        $deletedNames = [];
        $pageToken = null;

        do {
            $params = [
                'q' => $query,
                'fields' => 'nextPageToken, files(id, name, createdTime)',
                'pageSize' => 100,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ];

            if ($pageToken !== null) {
                $params['pageToken'] = $pageToken;
            }

            $list = $drive->files->listFiles($params);

            foreach ($list->getFiles() as $file) {
                $fileId = $file->getId();
                if (! $fileId) {
                    continue;
                }

                $drive->files->delete($fileId, ['supportsAllDrives' => true]);
                $deletedIds[] = $fileId;
                $deletedNames[] = (string) $file->getName();
            }

            $pageToken = $list->getNextPageToken();
        } while ($pageToken !== null);

        return [
            'deleted_count' => count($deletedIds),
            'deleted_ids' => $deletedIds,
            'deleted_names' => $deletedNames,
        ];
    }

    protected function uploadViaResumableChunks(
        GoogleClient $client,
        GoogleDrive $drive,
        DriveFile $metadata,
        string $localFilePath,
        int $fileSize,
        string $mimeType
    ): mixed {
        $handle = fopen($localFilePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Unable to open backup file for chunked upload: {$localFilePath}");
        }

        $client->setDefer(true);

        try {
            /** @var RequestInterface $request */
            $request = $drive->files->create($metadata, [
                'supportsAllDrives' => true,
                'fields' => 'id, name, size, createdTime',
            ]);

            $media = new MediaFileUpload(
                $client,
                $request,
                $mimeType,
                '',
                true,
                self::CHUNK_SIZE_BYTES
            );
            $media->setFileSize($fileSize);

            $status = false;
            while (! $status && ! feof($handle)) {
                $chunk = $this->readExactChunk($handle, self::CHUNK_SIZE_BYTES);
                if ($chunk === '') {
                    break;
                }
                $status = $media->nextChunk($chunk);
            }

            return $status;
        } finally {
            $client->setDefer(false);
            fclose($handle);
        }
    }

    /**
     * @param  resource  $handle
     */
    protected function readExactChunk($handle, int $chunkSize): string
    {
        $buffer = '';
        while (strlen($buffer) < $chunkSize && ! feof($handle)) {
            $data = fread($handle, $chunkSize - strlen($buffer));
            if ($data === false || $data === '') {
                break;
            }
            $buffer .= $data;
        }

        return $buffer;
    }

    protected function isOAuthConfigured(): bool
    {
        $clientId = trim((string) ($this->config['client_id'] ?? ''));
        $clientSecret = trim((string) ($this->config['client_secret'] ?? ''));
        $refreshToken = trim((string) ($this->config['refresh_token'] ?? ''));

        return $clientId !== '' && $clientSecret !== '' && $refreshToken !== '';
    }

    protected function createAuthenticatedClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName((string) config('app.name', 'Woldia University Dormitory Management'));

        if ($this->isOAuthConfigured()) {
            $client->setClientId(trim((string) $this->config['client_id']));
            $client->setClientSecret(trim((string) $this->config['client_secret']));
            $token = $client->fetchAccessTokenWithRefreshToken(trim((string) $this->config['refresh_token']));

            if (! is_array($token) || isset($token['error']) || empty($token['access_token'])) {
                $errorDetail = is_array($token)
                    ? (string) ($token['error_description'] ?? $token['error'] ?? 'Unknown OAuth token error')
                    : 'Invalid OAuth token response';
                throw new RuntimeException('Google Drive OAuth2 authentication failed: '.$errorDetail);
            }

            return $client;
        }

        $client->addScope(GoogleDrive::DRIVE);

        $rawPath = (string) ($this->config['credentials_path'] ?? storage_path('app/google/service-account.json'));
        $credentialsPath = $this->resolvePath($rawPath);

        if ($credentialsPath === '' || ! is_file($credentialsPath)) {
            throw new RuntimeException(
                "Google Drive credentials are not configured. Set GOOGLE_DRIVE_CLIENT_ID / CLIENT_SECRET / REFRESH_TOKEN or place a Service Account JSON key at '{$credentialsPath}'."
            );
        }

        $client->setAuthConfig($credentialsPath);

        return $client;
    }

    protected function resolveTargetFolderId(GoogleDrive $drive): string
    {
        $configuredFolderId = trim((string) ($this->config['folder_id'] ?? ''));
        if ($configuredFolderId !== '') {
            return $configuredFolderId;
        }

        $folderName = trim((string) ($this->config['folder_name'] ?? 'Dormitory_system')) ?: 'Dormitory_system';
        $query = sprintf(
            "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and trashed = false",
            $this->escapeDriveQueryLiteral($folderName)
        );

        $response = $drive->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'orderBy' => 'createdTime asc',
            'pageSize' => 1,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $files = $response->getFiles();
        if (! empty($files)) {
            return (string) $files[0]->getId();
        }

        // OAuth2 refresh tokens using the least-privilege drive.file scope can only see
        // folders created or opened by this OAuth client, so create the folder automatically.
        if ($this->isOAuthConfigured()) {
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'description' => sprintf(
                    'Automated PostgreSQL backup folder for %s',
                    config('app.name', 'Dormitory Management System')
                ),
            ]);

            $createdFolder = $drive->files->create($folderMetadata, [
                'fields' => 'id, name',
                'supportsAllDrives' => true,
            ]);

            if (! $createdFolder instanceof DriveFile || empty($createdFolder->getId())) {
                throw new RuntimeException("Unable to create target Google Drive folder '{$folderName}'.");
            }

            return (string) $createdFolder->getId();
        }

        throw new RuntimeException(
            "Target Google Drive folder '{$folderName}' could not be found. Set GOOGLE_DRIVE_FOLDER_ID in .env and share the folder with the Service Account email."
        );
    }

    protected function resolvePath(string $path): string
    {
        $trimmed = trim($path, " \t\n\r\0\x0B\"'");
        if ($trimmed === '') {
            return '';
        }

        // Absolute Unix path or Windows drive path (e.g. C:\... or D:/...)
        if (str_starts_with($trimmed, '/') || str_starts_with($trimmed, '\\') || preg_match('/^[A-Za-z]:[\/\\\\]/', $trimmed) === 1) {
            return $trimmed;
        }

        return base_path($trimmed);
    }

    protected function escapeDriveQueryLiteral(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }
}
