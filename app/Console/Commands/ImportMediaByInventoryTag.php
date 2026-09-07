<?php

namespace App\Console\Commands;

use App\Models\Marker;
use App\Models\Media;
use App\Models\MediaLibrary;
use App\Models\Tree;
use App\Services\HeicImageConverter;
use App\Services\MediaThumbnailGenerator;
use App\Services\UploadedImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

class ImportMediaByInventoryTag extends Command
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
        'image/heic' => 'webp',
        'image/heif' => 'webp',
        'image/heic-sequence' => 'webp',
        'image/heif-sequence' => 'webp',
    ];

    protected $signature = 'media:import-by-inventory-tag
                            {directory : Directory containing the image files}
                            {--dry-run : Validate and show what would be imported without writing anything}
                            {--recursive : Search for images in nested directories}';

    protected $description = 'Import images and attach them to markers by tree inventory tag';

    public function handle(
        HeicImageConverter $heicConverter,
        UploadedImageOptimizer $imageOptimizer,
        MediaThumbnailGenerator $thumbnailGenerator,
    ): int {
        $directory = realpath((string) $this->argument('directory'));

        if ($directory === false || ! is_dir($directory) || ! is_readable($directory)) {
            $this->error('The directory does not exist or is not readable.');

            return self::FAILURE;
        }

        $files = $this->imageFiles($directory);

        if ($files === []) {
            $this->info('No supported images were found.');

            return self::SUCCESS;
        }

        $markersByTag = $this->markersByInventoryTag();
        $nextOrder = [];
        $stats = [
            'imported' => 0,
            'skipped' => 0,
            'unmatched' => 0,
            'ambiguous' => 0,
            'failed' => 0,
        ];
        $messages = [];

        $progress = $this->output->createProgressBar(count($files));
        $progress->start();

        foreach ($files as $file) {
            [$tag, $markerIds] = $this->resolveInventoryTag($file, $markersByTag);

            if ($markerIds === []) {
                $stats['unmatched']++;
                $messages[] = "No marker for inventory tag '{$tag}': {$file->getFilename()}";
                $progress->advance();

                continue;
            }

            if (count($markerIds) > 1) {
                $stats['ambiguous']++;
                $messages[] = "Inventory tag '{$tag}' belongs to multiple markers: ".implode(', ', $markerIds);
                $progress->advance();

                continue;
            }

            $markerId = $markerIds[0];

            try {
                $result = $this->importFile(
                    $file,
                    $markerId,
                    $nextOrder,
                    $heicConverter,
                    $imageOptimizer,
                    $thumbnailGenerator,
                );
                $stats[$result]++;
            } catch (Throwable $exception) {
                report($exception);
                $stats['failed']++;
                $messages[] = "{$file->getFilename()}: {$exception->getMessage()}";
            }

            $progress->advance();
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
            [$this->option('dry-run') ? 'Would import' : 'Imported', 'Already attached', 'No marker', 'Ambiguous tag', 'Failed'],
            [[$stats['imported'], $stats['skipped'], $stats['unmatched'], $stats['ambiguous'], $stats['failed']]],
        );

        return $stats['failed'] === 0 && $stats['ambiguous'] === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    /**
     * @return array<int, SplFileInfo>
     */
    private function imageFiles(string $directory): array
    {
        $iterator = $this->option('recursive')
            ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS))
            : new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);

        $files = [];

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || ! $file->isReadable()) {
                continue;
            }

            $mimeType = mime_content_type($file->getPathname());

            if (is_string($mimeType) && isset(self::MIME_EXTENSIONS[$mimeType])) {
                $files[] = $file;
            }
        }

        usort($files, fn (SplFileInfo $a, SplFileInfo $b) => strnatcasecmp($a->getPathname(), $b->getPathname()));

        return $files;
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function markersByInventoryTag(): array
    {
        return Tree::query()
            ->join('markers', 'markers.id', '=', 'trees.id')
            ->whereNull('markers.deleted_at')
            ->whereNotNull('trees.inventory_tag')
            ->where('trees.inventory_tag', '<>', '')
            ->get(['trees.id', 'trees.inventory_tag'])
            ->reduce(function (array $markers, Tree $tree) {
                $markers[$this->normalizeTag($tree->inventory_tag)][] = $tree->id;

                return $markers;
            }, []);
    }

    /**
     * @param  array<string, array<int, int>>  $markersByTag
     * @return array{string, array<int, int>}
     */
    private function resolveInventoryTag(SplFileInfo $file, array $markersByTag): array
    {
        $tag = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $tag = trim($tag);
        $markerIds = $markersByTag[$this->normalizeTag($tag)] ?? [];

        if ($markerIds !== []) {
            return [$tag, $markerIds];
        }

        $baseTag = preg_replace('/_\d+$/u', '', $tag) ?? $tag;

        return [$baseTag, $markersByTag[$this->normalizeTag($baseTag)] ?? []];
    }

    private function normalizeTag(string $tag): string
    {
        return mb_strtolower(trim($tag), 'UTF-8');
    }

    /**
     * @param  array<int, int>  $nextOrder
     * @return 'imported'|'skipped'
     */
    private function importFile(
        SplFileInfo $file,
        int $markerId,
        array &$nextOrder,
        HeicImageConverter $heicConverter,
        UploadedImageOptimizer $imageOptimizer,
        MediaThumbnailGenerator $thumbnailGenerator,
    ): string {
        $mimeType = mime_content_type($file->getPathname());

        if (! is_string($mimeType) || ! isset(self::MIME_EXTENSIONS[$mimeType])) {
            throw new RuntimeException('Unsupported image type.');
        }

        $hash = hash_file('sha256', $file->getPathname());

        if (! is_string($hash)) {
            throw new RuntimeException('The image checksum could not be calculated.');
        }

        $uploadedFile = new UploadedFile(
            $file->getPathname(),
            $file->getFilename(),
            $mimeType,
            null,
            true,
        );
        $shouldConvert = $heicConverter->supports($uploadedFile)
            || $imageOptimizer->shouldOptimize($uploadedFile);
        $extension = $shouldConvert ? 'webp' : self::MIME_EXTENSIONS[$mimeType];
        $storedPath = "uploads/inv-tags/{$hash}.{$extension}";
        $existingMedia = MediaLibrary::query()
            ->where('file_path', $storedPath)
            ->where('type', 'image')
            ->first();
        $alreadyAttached = $existingMedia !== null && Media::query()
            ->where('media_library_id', $existingMedia->id)
            ->where('model_type', Marker::class)
            ->where('model_id', $markerId)
            ->exists();

        if ($alreadyAttached && $existingMedia->getRawOriginal('thumbnail_path') !== null) {
            $nextOrder[$markerId] = ($nextOrder[$markerId] ?? 0) + 1;

            return 'skipped';
        }

        if ($this->option('dry-run')) {
            return $alreadyAttached ? 'skipped' : 'imported';
        }

        $disk = Storage::disk('public');
        $createdOriginal = false;
        $createdThumbnail = null;

        try {
            if (! $disk->exists($storedPath)) {
                $this->storeOriginal(
                    $file,
                    $uploadedFile,
                    $storedPath,
                    $heicConverter,
                    $imageOptimizer,
                );
                $createdOriginal = true;
            }

            return DB::transaction(function () use (
                $storedPath,
                $markerId,
                &$nextOrder,
                $thumbnailGenerator,
                &$createdThumbnail,
            ) {
                $mediaLibrary = MediaLibrary::firstOrCreate(
                    ['file_path' => $storedPath, 'type' => 'image'],
                    ['thumbnail_path' => null],
                );

                if ($mediaLibrary->thumbnail_path === null) {
                    $createdThumbnail = $thumbnailGenerator->generate($mediaLibrary);
                    $mediaLibrary->thumbnail_path = $createdThumbnail;
                    $mediaLibrary->saveQuietly();
                }

                if (Media::query()
                    ->where('media_library_id', $mediaLibrary->id)
                    ->where('model_type', Marker::class)
                    ->where('model_id', $markerId)
                    ->exists()) {
                    $nextOrder[$markerId] = ($nextOrder[$markerId] ?? 0) + 1;

                    return 'skipped';
                }

                $order = $nextOrder[$markerId] ?? 0;

                Media::query()
                    ->where('model_type', Marker::class)
                    ->where('model_id', $markerId)
                    ->whereHas('mediaFile', fn ($query) => $query->where('type', 'image'))
                    ->where('order', '>=', $order)
                    ->increment('order');

                Media::create([
                    'media_library_id' => $mediaLibrary->id,
                    'model_type' => Marker::class,
                    'model_id' => $markerId,
                    'order' => $order,
                    'description' => '',
                ]);
                $nextOrder[$markerId] = $order + 1;

                return 'imported';
            });
        } catch (Throwable $exception) {
            if ($createdThumbnail !== null) {
                $disk->delete($createdThumbnail);
            }

            if ($createdOriginal) {
                $disk->delete($storedPath);
            }

            throw $exception;
        }
    }

    private function storeOriginal(
        SplFileInfo $file,
        UploadedFile $uploadedFile,
        string $storedPath,
        HeicImageConverter $heicConverter,
        UploadedImageOptimizer $imageOptimizer,
    ): void {
        $disk = Storage::disk('public');

        if ($heicConverter->supports($uploadedFile)) {
            $convertedPath = $heicConverter->convert($uploadedFile);

            $this->moveConvertedFile($convertedPath, $storedPath);

            return;
        }

        if ($imageOptimizer->shouldOptimize($uploadedFile)) {
            $convertedPath = $imageOptimizer->optimize($uploadedFile);

            $this->moveConvertedFile($convertedPath, $storedPath);

            return;
        }

        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('The image could not be opened.');
        }

        try {
            if (! $disk->put($storedPath, $stream)) {
                throw new RuntimeException('The image could not be stored.');
            }
        } finally {
            fclose($stream);
        }
    }

    private function moveConvertedFile(string $convertedPath, string $storedPath): void
    {
        $disk = Storage::disk('public');
        $directory = dirname($storedPath);

        if (! $disk->exists($directory) && ! $disk->makeDirectory($directory)) {
            $disk->delete($convertedPath);

            throw new RuntimeException('The destination directory could not be created.');
        }

        if (! $disk->move($convertedPath, $storedPath)) {
            $disk->delete($convertedPath);

            throw new RuntimeException('The converted image could not be moved.');
        }
    }
}
