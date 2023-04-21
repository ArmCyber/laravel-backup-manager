<?php

namespace ArmCyber\BackupManager\Drivers\Compression;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class GzipCompressionDriver extends AbstractCompressionDriver
{
    public const DRIVER_ID = 'gzip';
    public const DRIVER_FILE_EXTENSION = 'gz';
    private const PROCESS_TIMEOUT = 3600;

    public function compress($filename): string {
        $command = 'gzip "${:ARMCYBER_FILENAME}"';
        $env = $this->getEnv($filename);
        Process::fromShellCommandline($command)->setTimeout(self::PROCESS_TIMEOUT)->mustRun(null, $env);
        return $filename . '.gz';
    }

    public function extract($filename): string {
        $extension = '.' . self::DRIVER_FILE_EXTENSION;
        $filenameWithExtension = Str::finish($filename, $extension);
        if ($filenameWithExtension != $filename) {
            File::move($filename, $filenameWithExtension);
        }
        $command = 'gzip -d "${:ARMCYBER_FILENAME}"';
        $env = $this->getEnv($filename);
        Process::fromShellCommandline($command)->setTimeout(self::PROCESS_TIMEOUT)->mustRun(null, $env);
        return Str::beforeLast($filename, $extension);
    }

    private function getEnv($filename) {
        return [
            'ARMCYBER_FILENAME' => $filename,
        ];
    }
}
