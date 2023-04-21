<?php

namespace ArmCyber\BackupManager\Commands;

use ArmCyber\BackupManager\Services\BackupManagerService;
use Illuminate\Console\Command;

class RestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup-manager:restore {filename} {--connection=} {--disk=} {--no-wipe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $connection = $this->option('connection');
        $disk = $this->option('disk');
        $wipe = !$this->option('no-wipe');
        BackupManagerService::make($connection, $disk)->restore($filename, $wipe);

        return 0;
    }
}
