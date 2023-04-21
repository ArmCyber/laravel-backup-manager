<?php

namespace ArmCyber\BackupManager\Commands;

use ArmCyber\BackupManager\Services\BackupManagerService;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup-manager:list {--disk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all backups.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $disk = $this->option('disk');
        $files = BackupManagerService::make(null, $disk)->list();
        foreach ($files as $file) {
            $this->line($file);
        }
        return 0;
    }
}
