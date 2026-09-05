<?php

namespace App\Console\Commands;

use App\Models\MediaLibrary;
use App\Services\MediaThumbnailGenerator;
use Illuminate\Console\Command;
use Throwable;

class GenerateMediaThumbnails extends Command
{
    protected $signature = 'media:generate-thumbnails
                            {--force : Regenerate thumbnails that already exist}';

    protected $description = 'Generate missing WebP thumbnails for existing media library images';

    public function handle(MediaThumbnailGenerator $generator): int
    {
        $query = MediaLibrary::query()
            ->where('type', 'image')
            ->when(! $this->option('force'), fn ($query) => $query->whereNull('thumbnail_path'));

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No images need thumbnail generation.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $failures = [];
        $progress = $this->output->createProgressBar($total);
        $progress->start();

        $query->chunkById(100, function ($mediaItems) use ($generator, &$generated, &$skipped, &$failures, $progress) {
            foreach ($mediaItems as $media) {
                try {
                    $thumbnailPath = $generator->generate($media);

                    if ($thumbnailPath === null) {
                        $skipped++;
                    } else {
                        $media->thumbnail_path = $thumbnailPath;
                        $media->saveQuietly();
                        $generated++;
                    }
                } catch (Throwable $exception) {
                    $failures[] = "Media #{$media->getKey()}: {$exception->getMessage()}";
                }

                $progress->advance();
            }
        });

        $progress->finish();
        $this->newLine(2);

        foreach ($failures as $failure) {
            $this->warn($failure);
        }

        $this->table(
            ['Generated', 'Skipped', 'Failed'],
            [[$generated, $skipped, count($failures)]],
        );

        return $failures === [] ? self::SUCCESS : self::FAILURE;
    }
}
