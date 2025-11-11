<?php

namespace DuyunApp\DuyunBackupManager;

use DuyunApp\DuyunBackupManager\BackupService;
use DuyunApp\DuyunBackupManager\Console\CleanOldBackupsCommand;
use Illuminate\Support\ServiceProvider;

class BackupManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind("duyun.backup.manager", function () {
            return new BackupService();
        });
        $this->mergeConfigFrom(
            __DIR__ . '/config/duyun-backup.php',
            'duyun-backup'
        );
        $this->commands([
            CleanOldBackupsCommand::class,
        ]);
    }
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/duyun-backup.php' => config_path('duyun-backup.php'),
        ], 'duyun-backup-config');
    }
}
