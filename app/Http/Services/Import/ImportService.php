<?php

namespace App\Http\Services\Import;

use Illuminate\Http\UploadedFile;

class ImportService
{
    public function __construct(
        private readonly GeoJsonImporter   $geoJsonImporter,
        private readonly CsvImporter       $csvImporter,
        private readonly ShapefileImporter $shapefileImporter,
    ) {}

    public function import(UploadedFile $file, string $mode): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        return $this->resolveImporter($ext)->import($file, $mode);
    }

    public function preview(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        return $this->resolveImporter($ext)->preview($file);
    }

    private function resolveImporter(string $ext): ImporterInterface
    {
        return match ($ext) {
            'csv' => $this->csvImporter,
            'zip' => $this->shapefileImporter,
            default => $this->geoJsonImporter,
        };
    }
}
