<?php

namespace App\Http\Services\Import;

use Illuminate\Http\UploadedFile;

interface ImporterInterface
{
    public function import(UploadedFile $file, string $mode): array;
    public function preview(UploadedFile $file): array;
}
