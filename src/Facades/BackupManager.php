<?php

namespace DuyunApp\DuyunBackupManager\Facades;

use Illuminate\Support\Facades\Facade;

class BackupManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'duyun.backup.manager';
    }
}
