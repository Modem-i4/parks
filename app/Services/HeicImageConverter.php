<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use RuntimeException;

class HeicImageConverter
{
    private const MAX_BYTES = 1887436; // 1.8 MiB

    private const MAX_EDGE = 2560;

    private const MIN_EDGE = 640;

    private const RESIZE_FACTOR = 0.8;

    private const QUALITIES = [86, 78, 70, 62, 54, 46];

    private const MIME_TYPES = [
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence',
    ];

    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::MIME_TYPES, true);
    }

    public function convert(UploadedFile $file): string
    {
        if (! class_exists(Imagick::class) || Imagick::queryFormats('HEI*') === []) {
            throw new RuntimeException('ImageMagick HEIC/HEIF support is not available.');
        }

        $source = new Imagick;

        try {
            $source->readImage($file->getRealPath());
            $source->setIteratorIndex(0);
            $image = $source->getImage();
        } finally {
            $source->clear();
            $source->destroy();
        }

        try {
            $image->autoOrient();
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            $image->setImageFormat('webp');
            $image->stripImage();

            $originalMaxEdge = max($image->getImageWidth(), $image->getImageHeight());
            $maxEdge = min(self::MAX_EDGE, $originalMaxEdge);
            $minEdge = min(self::MIN_EDGE, $maxEdge);

            if ($originalMaxEdge > $maxEdge) {
                $image->thumbnailImage($maxEdge, $maxEdge, true);
            }

            while (true) {
                foreach (self::QUALITIES as $quality) {
                    $image->setImageCompressionQuality($quality);
                    $webp = $image->getImageBlob();

                    if (strlen($webp) <= self::MAX_BYTES) {
                        return $this->store($webp);
                    }
                }

                if ($maxEdge === $minEdge) {
                    break;
                }

                $maxEdge = max($minEdge, (int) floor($maxEdge * self::RESIZE_FACTOR));
                $image->thumbnailImage($maxEdge, $maxEdge, true);
            }
        } finally {
            $image->clear();
            $image->destroy();
        }

        throw new RuntimeException('The HEIC/HEIF image could not be compressed below 1.8 MiB.');
    }

    private function store(string $webp): string
    {
        $path = 'uploads/'.Str::uuid().'.webp';

        if (! Storage::disk('public')->put($path, $webp)) {
            throw new RuntimeException('The converted WebP image could not be stored.');
        }

        return $path;
    }
}
