# Backup Manager package for Laravel

## Installation
1. Add this repository to your composer.json repositories
2. Run `composer require armcyber/backup-manager`

## Usage
Currently, only PostgreSQL is supported.

### Using the CLI
#### Creating a backup
You can use command `php artisan backup-manager:backup filename.sql` to create a backup.
This will use your default database connection and default storage disk to store your backup. Optionally, you can use
`--connection=CONNECTION_NAME` and `--disk=s3` options to change these options.
You can also compress your dump to GZIP using `--compression=gzip` option.

#### Restoring a backup
Use the command `php artisan backup-manager:restore filename.sql` to restore your backup. 
Here, you can also use `--connection` and `--disk` options. The command will automatically detect `gzip` compression and
decompress before restoring.
Note: This command will wipe your database.

#### Showing all backups
Use the command `php artisan backup-manager:list`.

### Using the Service
You can also use the BackupManagerService in the code to manage your backups.
For that, you need to create a service instance first.
```php
use ArmCyber\BackupManager\Services\BackupManagerService;
...

$backupManagerService = new BackupManagerService($connection, $disk);
```
`$connection` and `$disk` arguments are optional, and defaulted to null, which means, that the service will use your default
configuration if these parameters will be ignored.

#### Creating a backup
Use `$backupManagerService->backup($filename)` to create a backup, you can pass second parameter `$compression` to compress
your backup.

#### Restoring the backup
Use `$backupManagerService->restore($filename)` to restore your backup.

#### Getting a list of all available backup files
Use `$backupManagerService->list()`.

Enjoy!