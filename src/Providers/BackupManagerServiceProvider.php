<?php

namespace GoodSales\BackupManager\Providers;

use GoodSales\BackupManager\Commands\BackupCommand;
use GoodSales\BackupManager\Commands\ListCommand;
use GoodSales\BackupManager\Commands\RestoreCommand;
use GoodSales\BackupManager\Services\OutputService;
use Illuminate\Support\ServiceProvider;

class BackupManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OutputService::class);
        $this->mergeConfigFrom(__DIR__ . '/../../config/backup-manager.php', 'backup-manager');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/backup-manager.php' => config_path('backup-manager.php'),
        ], 'goodsales-backup-manager');
        $this->commands([
            BackupCommand::class,
            RestoreCommand::class,
            ListCommand::class,
        ]);
    }
}
