<?php

namespace DuyunApp\DuyunBackupManager;

use DuyunApp\DuyunBackupManager\Contracts\ExporterInterface;
use DuyunApp\DuyunBackupManager\Exporters\ExcelExporter;
use DuyunApp\DuyunBackupManager\Exporters\CsvExporter;
use DuyunApp\DuyunBackupManager\Exporters\ModelExporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupService
{
    protected string $modelClass;
    protected array $filters = [];
    protected array $columns = ['*'];
    protected string $disk;
    protected string $directory;
    protected string $fileName = '';
    protected ?ExporterInterface $exporter = null;

    public function __construct()
    {
        $this->disk = config('duyun-backup.disk', 'local');
        $this->directory = config('duyun-backup.path', 'backups');
        $this->deleteAfterBackup = config('duyun-backup.auto_delete', false);
    }

    public static function for(string $modelClass): self
    {
        $instance = new self;
        $instance->modelClass = $modelClass;
        return $instance;
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function fileName(string $name): self
    {
        $this->fileName = $name;
        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    public function directory(string $directory): self
    {
        $this->directory = trim($directory, '/');
        return $this;
    }



    /**
     * تحديد نوع الـ Exporter المستخدم
     */
    public function exporter(?ExporterInterface $exporter = null): self
    {
        $this->exporter = $exporter;
        return $this;
    }

    public function run(): string
    {
        return DB::transaction(function (): bool|string {

            $modelExporter = ModelExporter::for($this->modelClass)
                ->filters($this->filters)
                ->columns($this->columns);

            $exporter = $this->exporter ?? $this->resolveDefaultExporter();

            $filename = $this->fileName
                ?: config('duyun-backup.file_prefix', 'backup_')
                . Str::slug(class_basename($this->modelClass))
                . '-' . now()->format('Ymd_His')
                . '.' . $exporter->getExtension();

            $disk = Storage::disk($this->disk);

            $path = $disk->path("{$this->directory}/{$filename}");

            $disk->makeDirectory($this->directory);

            $data = $modelExporter->get();

            if (empty($data)) {
                return false;
            }

            $exporter->setData($data)->export($path);

            $disk->put("delete/" . "d-" . rand(100, 9999) . ".json", json_encode([
                "model" => $this->modelClass,
                "ids" => array_column($data, 'id')
            ]));

            return $path;
        });
    }



    protected function resolveDefaultExporter(): ExporterInterface
    {
        return match (config('duyun-backup.default_exporter', 'excel')) {
            'csv' => new CsvExporter(),
            default => new ExcelExporter(),
        };
    }
}
