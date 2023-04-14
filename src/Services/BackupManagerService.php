<?php

namespace GoodSales\BackupManager\Services;

use GoodSales\BackupManager\Drivers\Compression\AbstractCompressionDriver;
use GoodSales\BackupManager\Drivers\Database\AbstractDatabaseDriver;
use GoodSales\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class BackupManagerService
{
    private AbstractDatabaseDriver $database;
    private OutputService $output;
    private StorageService $storage;
    private WorkingDirService $workingDir;


    public static function make($connection = null, $disk = null)
    {
        return new self($connection, $disk);
    }

    private function __construct($connection = null, $disk = null)
    {
        $this->output = App::make(OutputService::class);
        $this->workingDir = new WorkingDirService();
        $this->storage = new StorageService($disk, $this->workingDir);
        $this->database = AbstractDatabaseDriver::make($connection, $this->workingDir);
    }

    public function backup(string $filename, $compression = null)
    {
        try {
            if ($compression) {
                $compressor = AbstractCompressionDriver::make($compression);
            }

            // Create backup file
            $this->output->write('Creating backup file.');
            $backupFile = $this->database->createBackup();

            // Compress
            if (isset($compressor)) {
                $this->output->write('Compressing.');
                $backupFile = $compressor->compress($backupFile);
            }

            // Store
            $this->output->write('Storing the backup.');
            $this->storage->store($backupFile, $filename);

            $this->output->write('Done, cleaning up.', 'info');
        } catch (BackupManagerRuntimeException $exception) {
            $this->output->throw($exception);
        } finally {
            $this->workingDir->delete();
        }
    }

    public function restore(string $filename, $wipeDb = true)
    {
        try {
            // Download backup file
            $this->output->write('Downloading the backup.');
            $filename = $this->storage->downloadToTmp($filename);

            // Decompress
            $compressor = AbstractCompressionDriver::makeForFileIfNeeded($filename);
            if ($compressor !== null) {
                $this->output->write('Extracting.');
                $filename = $compressor->extract($filename);
            }

            // Import
            $this->output->write('Importing.');
            $this->database->restoreBackup($filename, $wipeDb);

            $this->output->write('Done, cleaning up.', 'info');
        } catch (BackupManagerRuntimeException $exception) {
            $this->output->throw($exception);
        } finally {
            $this->workingDir->delete();
        }
    }

    public function list()
    {
        return Collection::make($this->storage->allFiles())->filter(function($item) {
            return !Str::endsWith($item, '.gitignore');
        })->values()->all();
    }
}
