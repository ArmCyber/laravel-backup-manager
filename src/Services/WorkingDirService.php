<?php

namespace ArmCyber\BackupManager\Services;

use ArmCyber\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkingDirService
{
    private const TEMP_PREFIX = 'backup-temp-';

    private $directory;

    public function __construct()
    {
        $path = config('backup-manager.working_dir');
        if (!$path || !File::exists(File::dirname($path))) {
            throw new BackupManagerRuntimeException('Working directory is not configured or is invalid.');
        }
        $this->directory = Str::finish($path, DIRECTORY_SEPARATOR) . $this->getTempName() . DIRECTORY_SEPARATOR;

    }

    public function getDirectory()
    {
        File::ensureDirectoryExists($this->directory);
        return $this->directory;
    }

    public function delete()
    {
        File::deleteDirectory($this->directory);
    }

    public function file($extension = null)
    {
        $filename = $this->getDirectory() . $this->getTempName();
        if ($extension) {
            $extension = Str::start($extension, '.');
            $filename .= $extension;
        }
        return $filename;
    }

    public function writeStreamToTempFile($stream, $extension = null)
    {
        $disk = $this->createStorageDisk();
        $basename = $this->getTempName();
        if ($extension) {
            $extension = Str::start($extension, '.');
            $basename .= $extension;
        }
        $disk->writeStream($basename, $stream);
        return $this->getDirectory() . $basename;
    }

    private function getTempName()
    {
        return self::TEMP_PREFIX . Str::lower(Str::random());
    }

    private function createStorageDisk() {
        return Storage::build([
            'driver' => 'local',
            'root' => $this->getDirectory(),
            'throw' => true,
        ]);
    }
}
