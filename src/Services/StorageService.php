<?php

namespace ArmCyber\BackupManager\Services;

use ArmCyber\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    private FileSystem $disk;
    private WorkingDirService $workingDir;

    public function __construct($disk = null, WorkingDirService $workingDir = null)
    {
        $this->disk = Storage::disk($disk);
        $this->workingDir = $workingDir;
    }

    public function store($file, $destination)
    {
        $filename = File::basename($destination);
        $dir = File::dirname($destination);
        $this->disk->putFileAs($dir, $file, $filename);
    }

    public function downloadToTmp($filename)
    {
        if (!$this->disk->exists($filename)) {
            throw new BackupManagerRuntimeException("Backup file '{$filename}' does not exist.");
        }
        $stream = $this->disk->readStream($filename);
        $extension = File::extension($filename);
        return $this->workingDir->writeStreamToTempFile($stream, $extension);
    }

    public function allFiles()
    {
        return $this->disk->allFiles();
    }
}
