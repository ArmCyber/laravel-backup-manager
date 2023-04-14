<?php

namespace GoodSales\BackupManager\Commands;

use GoodSales\BackupManager\Services\BackupManagerService;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup-manager:backup {filename} {--connection=} {--disk=} {--compression=}';

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
        $compression = $this->option('compression');
        BackupManagerService::make($connection, $disk)->backup($filename, $compression);

        return 0;
    }
}
