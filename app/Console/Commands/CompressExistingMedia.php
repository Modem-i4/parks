<?php

namespace App\Console\Commands;

use App\Models\MediaLibrary;
use App\Models\News;
use App\Services\ImageWebpConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CompressExistingMedia extends Command
{
    private const MIN_BYTES = 2097152; // 2 MiB

    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/bmp',
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence',
    ];

    protected $signature = 'media:compress-existing
                            {--dry-run : Show what would be compressed without changing files or the database}
                            {--path= : Compress only this media library path}';

    protected $description = 'Compress media library images larger than 2 MiB to WebP';

    public function handle(ImageWebpConverter $converter): int
    {
        $paths = MediaLibrary::query()
            ->where('type', 'image')
            ->where('file_path', 'like', 'uploads/%')
            ->where('file_path', 'not like', 'uploads/thumbnails/%')
            ->when($this->option('path'), fn ($query, $path) => $query->where('file_path', $path))
            ->select('file_path')
            ->distinct()
            ->toBase()
            ->pluck('file_path');

        if ($paths->isEmpty()) {
            $this->info('No media library images were found in uploads.');

            return self::SUCCESS;
        }

        $stats = [
            'compressed' => 0,
            'small' => 0,
            'unsupported' => 0,
            'missing' => 0,
            'failed' => 0,
        ];
        $bytesBefore = 0;
        $bytesAfter = 0;
        $messages = [];
        $disk = Storage::disk('public');
        $progress = $this->output->createProgressBar($paths->count());
        $progress->start();

        foreach ($paths as $storedPath) {
            try {
                if (! $disk->exists($storedPath)) {
                    $stats['missing']++;
                    $messages[] = "Missing file: {$storedPath}";

                    continue;
                }

                $size = $disk->size($storedPath);

                if ($size <= self::MIN_BYTES) {
                    $stats['small']++;

                    continue;
                }

                $mimeType = mime_content_type($disk->path($storedPath));

                if (! is_string($mimeType) || ! in_array($mimeType, self::SUPPORTED_MIME_TYPES, true)) {
                    $stats['unsupported']++;
                    $messages[] = "Unsupported image type '{$mimeType}': {$storedPath}";

                    continue;
                }

                $bytesBefore += $size;

                if ($this->option('dry-run')) {
                    $stats['compressed']++;

                    continue;
                }

                $newPath = $converter->convert($disk->path($storedPath));
                $newSize = $disk->size($newPath);

                try {
                    $this->replaceReferences($storedPath, $newPath);
                } catch (Throwable $exception) {
                    $disk->delete($newPath);

                    throw $exception;
                }

                if (! $disk->delete($storedPath)) {
                    try {
                        $this->replaceReferences($newPath, $storedPath);
                    } finally {
                        $disk->delete($newPath);
                    }

                    throw new RuntimeException('The old file could not be deleted.');
                }

                $bytesAfter += $newSize;
                $stats['compressed']++;
            } catch (Throwable $exception) {
                report($exception);
                $stats['failed']++;
                $messages[] = "{$storedPath}: {$exception->getMessage()}";
            } finally {
                $progress->advance();
            }
        }

        $progress->finish();
        $this->newLine(2);

        foreach (array_slice($messages, 0, 50) as $message) {
            $this->warn($message);
        }

        if (count($messages) > 50) {
            $this->warn((count($messages) - 50).' additional messages were omitted.');
        }

        $this->table(
            [$this->option('dry-run') ? 'Would compress' : 'Compressed', 'Below 2 MiB', 'Unsupported', 'Missing', 'Failed'],
            [[$stats['compressed'], $stats['small'], $stats['unsupported'], $stats['missing'], $stats['failed']]],
        );

        if ($bytesBefore > 0) {
            $rows = [['Input size', $this->formatBytes($bytesBefore)]];

            if (! $this->option('dry-run')) {
                $rows[] = ['Compressed size', $this->formatBytes($bytesAfter)];
                $rows[] = ['Space saved', $this->formatBytes(max(0, $bytesBefore - $bytesAfter))];
            }

            $this->table(['Metric', 'Value'], $rows);
        }

        return $stats['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function replaceReferences(string $oldPath, string $newPath): void
    {
        DB::transaction(function () use ($oldPath, $newPath) {
            $updated = MediaLibrary::query()
                ->where('file_path', $oldPath)
                ->update(['file_path' => $newPath]);

            if ($updated === 0) {
                throw new RuntimeException('No media library references were updated.');
            }

            $oldUrl = '/storage/'.ltrim($oldPath, '/');
            $newUrl = '/storage/'.ltrim($newPath, '/');

            News::query()
                ->where('body', 'like', "%{$oldUrl}%")
                ->select(['id', 'body'])
                ->chunkById(100, function ($newsItems) use ($oldUrl, $newUrl) {
                    foreach ($newsItems as $news) {
                        DB::table('news')
                            ->where('id', $news->id)
                            ->update(['body' => str_replace($oldUrl, $newUrl, $news->body)]);
                    }
                });
        });
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB'];
        $value = $bytes;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return number_format($value, $unit === 0 ? 0 : 2).' '.$units[$unit];
    }
}
