<?php

namespace App\Services;

use App\Models\MediaLibrary;
use GdImage;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaThumbnailGenerator
{
    private const MAX_EDGE = 480;

    private const QUALITY = 72;

    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/bmp',
    ];

    public function generate(MediaLibrary $media): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new RuntimeException('The GD extension with WebP support is required.');
        }

        $sourcePath = $this->resolveSourcePath($media->getRawOriginal('file_path'));
        $mimeType = mime_content_type($sourcePath);

        if (! in_array($mimeType, self::SUPPORTED_MIME_TYPES, true)) {
            return null;
        }

        $contents = file_get_contents($sourcePath);
        $source = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $source instanceof GdImage) {
            throw new RuntimeException('The image could not be decoded.');
        }

        try {
            if ($mimeType === 'image/jpeg') {
                $source = $this->applyExifOrientation($source, $sourcePath);
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min(1, self::MAX_EDGE / max($width, $height));
            $thumbnailWidth = max(1, (int) round($width * $scale));
            $thumbnailHeight = max(1, (int) round($height * $scale));
            $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

            if (! $thumbnail instanceof GdImage) {
                throw new RuntimeException('The thumbnail canvas could not be created.');
            }

            try {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                imagefill($thumbnail, 0, 0, $transparent);
                imagecopyresampled(
                    $thumbnail,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $thumbnailWidth,
                    $thumbnailHeight,
                    $width,
                    $height,
                );

                ob_start();
                $written = imagewebp($thumbnail, null, self::QUALITY);
                $webp = ob_get_clean();

                if (! $written || ! is_string($webp) || $webp === '') {
                    throw new RuntimeException('The WebP thumbnail could not be encoded.');
                }
            } finally {
                imagedestroy($thumbnail);
            }
        } finally {
            imagedestroy($source);
        }

        $thumbnailPath = "uploads/thumbnails/{$media->getKey()}.webp";

        if (! Storage::disk('public')->put($thumbnailPath, $webp)) {
            throw new RuntimeException('The thumbnail could not be written to the public disk.');
        }

        return $thumbnailPath;
    }

    private function resolveSourcePath(string $storedPath): string
    {
        $path = ltrim($storedPath, '/');
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $disk->path($path);
        }

        $publicRoot = realpath(public_path());
        $publicFile = realpath(public_path($path));

        if ($publicRoot !== false
            && $publicFile !== false
            && str_starts_with($publicFile, $publicRoot.DIRECTORY_SEPARATOR)
            && is_file($publicFile)) {
            return $publicFile;
        }

        throw new RuntimeException("Source file not found: {$storedPath}");
    }

    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path, 'IFD0');
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
