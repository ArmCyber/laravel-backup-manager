<?php

namespace GoodSales\BackupManager\Drivers\Database;

use GoodSales\BackupManager\Exceptions\BackupManagerRuntimeException;
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
        $command = 'pg_dump --clean --host="${:GOODSALES_HOST}" --port="${:GOODSALES_PORT}" --username="${:GOODSALES_USER}" --dbname="${:GOODSALES_DATABASE}" > "${:GOODSALES_FILENAME}"';
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
        $command = 'psql --host="${:GOODSALES_HOST}" --port="${:GOODSALES_PORT}" --user="${:GOODSALES_USER}" --dbname="${:GOODSALES_DATABASE}" --file="${:GOODSALES_FILENAME}"';
        $env = $this->getEnv($filename);
        Process::fromShellCommandline($command)->setTimeout(self::PROCESS_TIMEOUT)->mustRun(null, $env);
    }

    private function getEnv($filename)
    {
        $config = $this->connection->getConfig();
        return [
            'GOODSALES_HOST' => $config['host'] ?? '',
            'GOODSALES_PORT' => $config['port'] ?? '',
            'GOODSALES_USER' => $config['username'] ?? '',
            'PGPASSWORD' => $config['password'] ?? '',
            'GOODSALES_DATABASE' => $config['database'],
            'GOODSALES_FILENAME' => $filename,
        ];
    }
}
