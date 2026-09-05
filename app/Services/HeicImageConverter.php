<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Imagick;
use RuntimeException;

class HeicImageConverter
{
    private const MIME_TYPES = [
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence',
    ];

    public function __construct(private readonly ImageWebpConverter $converter) {}

    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::MIME_TYPES, true);
    }

    public function convert(UploadedFile $file): string
    {
        if (! class_exists(Imagick::class) || Imagick::queryFormats('HEI*') === []) {
            throw new RuntimeException('ImageMagick HEIC/HEIF support is not available.');
        }

        return $this->converter->convert($file->getRealPath());
    }
}
