<?php

namespace ArmCyber\BackupManager\Drivers\Database;

use ArmCyber\BackupManager\Exceptions\BackupManagerRuntimeException;
use ArmCyber\BackupManager\Services\WorkingDirService;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class AbstractDatabaseDriver
{
    private const DRIVER_MAP = [
        PostgreSQLDriver::DRIVER_ID => PostgreSQLDriver::class,
    ];

    protected Connection $connection;
    protected WorkingDirService $workingDir;

    public function __construct(Connection $connection, WorkingDirService $workingDir)
    {
        $this->connection = $connection;
        $this->workingDir = $workingDir;
    }

    public static function make($connectionName, WorkingDirService $workingDir): static
    {
        $connection = DB::connection($connectionName);
        $driver = $connection->getConfig('driver');
        if (!array_key_exists($driver, self::DRIVER_MAP)) {
            throw new BackupManagerRuntimeException("Database driver '{$driver}' is not supported.");
        }
        $driverClass = self::DRIVER_MAP[$driver];
        return new $driverClass($connection, $workingDir);
    }

    abstract public function createBackup(): string;
    abstract public function restoreBackup($filename, $wipeDb = true): void;

    public function wipeDb() {
        Artisan::call('db:wipe', [
            '--database' => $this->connection->getName(),
        ]);
    }
}
