<?php

namespace DuyunApp\DuyunBackupManager\Contracts;

interface ExporterInterface
{
    /**
     * تحديد البيانات لتصديرها
     */
    public function setData(array $data): static|self;

    /**
     * تحديد الأعمدة المخصصة
     */
    // public function setColumns(array $columns): static;

    /**
     * تنفيذ عملية التصدير
     */
    public function export(string $path): string;


    public function getExtension(): string;
}
