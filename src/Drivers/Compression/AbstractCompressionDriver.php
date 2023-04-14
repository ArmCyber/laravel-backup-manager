<?php

namespace GoodSales\BackupManager\Drivers\Compression;

use GoodSales\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Support\Facades\File;

abstract class AbstractCompressionDriver
{
    public const DRIVER_MAP = [
        GzipCompressionDriver::DRIVER_ID => GzipCompressionDriver::class,
    ];

    public static function make($driver): static
    {
        if (!array_key_exists($driver, self::DRIVER_MAP)) {
            throw new BackupManagerRuntimeException("Database driver '{$driver}' is not supported.");
        }
        $driverClass = self::DRIVER_MAP[$driver];
        return new $driverClass();
    }

    public static function makeForFileIfNeeded($filename): ?static
    {
        $extension = File::guessExtension($filename);
        foreach (self::DRIVER_MAP as $driverClass) {
            if ($driverClass::DRIVER_FILE_EXTENSION == $extension) {
                return new $driverClass();
            }
        }
        return null;
    }

    abstract public function compress($filename): string;

    abstract public function extract($filename): string;
}
