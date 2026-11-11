<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupProcessRunner
{
    /**
     * Execute an external binary command without shell interpolation.
     *
     * @param  list<string>  $command
     * @param  array<string, string>  $env
     */
    public function run(array $command, array $env = [], int $timeoutSeconds = 600): string
    {
        $resolvedEnv = $this->mergeEssentialPlatformEnv($env);

        $process = new Process($command, null, null, null, $timeoutSeconds);
        $process->run(null, $resolvedEnv);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Ensure essential Windows system environment variables (such as SystemRoot and TEMP)
     * remain intact even when running inside `php artisan serve`, whose ServeCommand strips
     * case-sensitive Windows env keys before launching the PHP CLI server.
     *
     * @param  array<string, string>  $env
     * @return array<string, string>
     */
    public function mergeEssentialPlatformEnv(array $env): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return $env;
        }

        if (! $this->hasEnvKey($env, 'SystemRoot')) {
            $systemRoot = $this->resolveWindowsSystemRoot();
            if ($systemRoot !== '') {
                $env['SystemRoot'] = $systemRoot;
            }
        }

        if (! $this->hasEnvKey($env, 'windir')) {
            $windir = getenv('windir') ?: ($env['SystemRoot'] ?? $this->resolveWindowsSystemRoot());
            if ($windir !== '') {
                $env['windir'] = $windir;
            }
        }

        $writableTemp = $this->resolveWritableTempDir();
        if (! $this->hasEnvKey($env, 'TEMP') && $writableTemp !== '') {
            $env['TEMP'] = $writableTemp;
        }

        if (! $this->hasEnvKey($env, 'TMP') && $writableTemp !== '') {
            $env['TMP'] = $writableTemp;
        }

        return $env;
    }

    /**
     * @param  array<string, string>  $env
     */
    protected function hasEnvKey(array $env, string $needle): bool
    {
        foreach ($env as $key => $value) {
            if (strcasecmp((string) $key, $needle) === 0 && $value !== '' && $value !== false) {
                return true;
            }
        }

        return false;
    }

    protected function resolveWindowsSystemRoot(): string
    {
        foreach (['SystemRoot', 'SYSTEMROOT', 'windir'] as $var) {
            $val = getenv($var);
            if (is_string($val) && $val !== '' && is_dir($val)) {
                return $val;
            }
        }

        $systemDrive = getenv('SystemDrive');
        if (is_string($systemDrive) && $systemDrive !== '' && is_dir($systemDrive.'\\Windows')) {
            return $systemDrive.'\\Windows';
        }

        return is_dir('C:\\Windows') ? 'C:\\Windows' : '';
    }

    protected function resolveWritableTempDir(): string
    {
        $windowsRoot = $this->resolveWindowsSystemRoot();

        foreach (['TEMP', 'TMP'] as $var) {
            $val = getenv($var);
            if (is_string($val) && $val !== '' && strcasecmp(rtrim($val, '\\/'), rtrim($windowsRoot, '\\/')) !== 0 && @is_dir($val)) {
                return $val;
            }
        }

        $sysTemp = sys_get_temp_dir();
        if (is_string($sysTemp) && $sysTemp !== '' && strcasecmp(rtrim($sysTemp, '\\/'), rtrim($windowsRoot, '\\/')) !== 0 && @is_dir($sysTemp)) {
            return $sysTemp;
        }

        $fallback = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'backups';
        if (! is_dir($fallback)) {
            @mkdir($fallback, 0755, true);
        }

        return is_dir($fallback) ? $fallback : '';
    }
}
