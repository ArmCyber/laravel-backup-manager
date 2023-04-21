<?php

namespace ArmCyber\BackupManager\Drivers\Database;

use ArmCyber\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class PostgreSQLDriver extends AbstractDatabaseDriver
{
    public const DRIVER_ID = 'pgsql';
    public const DRIVER_FILE_EXTENSION = 'sql';
    private const PROCESS_TIMEOUT = 14400;

    public function createBackup(): string
    {
        $filename = $this->workingDir->file(self::DRIVER_FILE_EXTENSION);
        $command = 'pg_dump --clean --host="${:ARMCYBER_HOST}" --port="${:ARMCYBER_PORT}" --username="${:ARMCYBER_USER}" --dbname="${:ARMCYBER_DATABASE}" > "${:ARMCYBER_FILENAME}"';
        $env = $this->getEnv($filename);
        Process::fromShellCommandline($command)->setTimeout(self::PROCESS_TIMEOUT)->mustRun(null, $env);
        if (!File::exists($filename)) {
            throw new BackupManagerRuntimeException('Failed to create a backup, backup file doesn\t exist.');
        }
        return $filename;
    }

    public function restoreBackup($filename, $wipeDb = true): void
    {
        $extension = '.' . self::DRIVER_FILE_EXTENSION;
        $filenameWithExtension = Str::finish($filename, $extension);
        if ($filename !== $filenameWithExtension) {
            File::move($filename, $filenameWithExtension);
            $filename = $filenameWithExtension;
        }
        if ($wipeDb) {
            $this->wipeDb();
        }
        $command = 'psql --host="${:ARMCYBER_HOST}" --port="${:ARMCYBER_PORT}" --user="${:ARMCYBER_USER}" --dbname="${:ARMCYBER_DATABASE}" --file="${:ARMCYBER_FILENAME}"';
        $env = $this->getEnv($filename);
        Process::fromShellCommandline($command)->setTimeout(self::PROCESS_TIMEOUT)->mustRun(null, $env);
    }

    private function getEnv($filename)
    {
        $config = $this->connection->getConfig();
        return [
            'ARMCYBER_HOST' => $config['host'] ?? '',
            'ARMCYBER_PORT' => $config['port'] ?? '',
            'ARMCYBER_USER' => $config['username'] ?? '',
            'PGPASSWORD' => $config['password'] ?? '',
            'ARMCYBER_DATABASE' => $config['database'],
            'ARMCYBER_FILENAME' => $filename,
        ];
    }
}
