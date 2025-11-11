<?php

namespace DuyunApp\DuyunBackupManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

class CleanOldBackupsCommand extends Command
{
    protected $signature = 'duyun:clean-backups {--days=7 : عدد الأيام قبل الحذف}';
    protected $description = 'حذف النسخ الاحتياطية القديمة من المسار المحدد';



    public function handle(): void
    {
        $disk = config('duyun-backup.disk', 'public');
        $days = (int) $this->option('days');

        // $this->info("🔍 البحث عن النسخ الأقدم من {$days} يوم في قرص {$disk}...");

        $storage = Storage::disk($disk);
        $files = $storage->allFiles("delete");
        foreach ($files as $e) {
            try {
                $file = json_decode($storage->get($e), 1);
                $model = $file['model'];
                ($model)::whereIn("id", $file['ids'])->delete();
                Log::info("delete f", ["model" => $model, "delete" => $file['ids']]);
                $storage->delete($e);
            } catch (Exception $e) {
                Log::error("delete", ["errerMessage" => $e->getMessage()]);
                continue;
            }
        }
        // Log::info("delete fold", ["delete" => ]);
        $this->info("✅");
    }
}
