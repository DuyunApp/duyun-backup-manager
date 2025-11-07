<?php

namespace DuyunApp\DuyunBackupManager\Exporters;

use DuyunApp\DuyunBackupManager\Contracts\ExporterInterface;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExcelExporter implements ExporterInterface
{
    protected array $data = [];

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function export(string $path): string
    {
        
        if (!str_ends_with($path, '.xlsx')) {
            $path .= '.xlsx';
        }

        $writer = SimpleExcelWriter::create($path);
        $writer->addRows($this->data);
        $writer->close();

        return $path;
    }


    public function getExtension(): string
    {
        return 'xlsx';
    }
}
