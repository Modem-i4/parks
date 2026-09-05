<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class UploadedImageOptimizer
{
    private const MAX_BYTES = 1887436; // 1.8 MiB

    private const MAX_EDGE = 2560;

    private const MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/bmp',
    ];

    public function __construct(private readonly ImageWebpConverter $converter) {}

    public function shouldOptimize(UploadedFile $file): bool
    {
        if (! in_array($file->getMimeType(), self::MIME_TYPES, true)) {
            return false;
        }

        if (($file->getSize() ?: 0) > self::MAX_BYTES) {
            return true;
        }

        $dimensions = @getimagesize($file->getRealPath());

        return is_array($dimensions)
            && max((int) $dimensions[0], (int) $dimensions[1]) > self::MAX_EDGE;
    }

    public function optimize(UploadedFile $file): string
    {
        if (! in_array($file->getMimeType(), self::MIME_TYPES, true)) {
            throw new RuntimeException('The image format cannot be optimized.');
        }

        return $this->converter->convert(
            $file->getRealPath(),
            min(self::MAX_BYTES, $file->getSize() ?: self::MAX_BYTES),
        );
    }
}
