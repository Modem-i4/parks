<?php

namespace App\Http\Controllers;

use App\Models\MediaLibrary;
use App\Services\HeicImageConverter;
use App\Services\MediaThumbnailGenerator;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'image';
        return MediaLibrary::orderByDesc('created_at')->where('type', $type)->get();
    }

    public function store(
        Request $request,HeicImageConverter $heicConverter,MediaThumbnailGenerator $thumbnailGenerator,
    ) {
        $request->validate([
            'file' => 'required|mimetypes:image/jpeg,image/png,image/webp,image/bmp,image/gif,image/svg+xml,image/svg,image/heic,image/heif,image/heic-sequence,image/heif-sequence|max:10240',
            'thumbnail' => 'nullable|mimetypes:image/webp|max:512',
            'type' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $isHeic = $heicConverter->supports($file);
        if ($isHeic) {
            try {
                $path = $heicConverter->convert($file);
            } catch (\Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['file' => 'Не вдалося обробити HEIC/HEIF зображення.']);
            }
        } elseif (in_array($mime, ['image/svg+xml', 'image/svg'])) {
            $cleanSvg = $this->sanitizeSvg($file);
            $filename = uniqid('svg_', true).'.svg';
            $path = 'uploads/'.$filename;
            Storage::disk('public')->put($path, $cleanSvg);
        } else {
            $path = $file->store('uploads', 'public');
        }

        $thumbnailPath = $request->file('thumbnail')?->store('uploads/thumbnails', 'public');

        $mediaFile = MediaLibrary::create([
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'type' => $request->type ?? 'image',
        ]);

        if ($thumbnailPath === null && $isHeic) {
            try {
                $mediaFile->thumbnail_path = $thumbnailGenerator->generate($mediaFile);
                $mediaFile->saveQuietly();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json($mediaFile, 201);
    }

    public function sanitizeSvg(UploadedFile $file): string
    {
        $svg = file_get_contents($file->getRealPath());

        $sanitizer = new Sanitizer;
        $cleanSvg = $sanitizer->sanitize($svg);

        if ($cleanSvg === false) {
            throw new \Exception('Invalid or uncleanable SVG.');
        }

        return $cleanSvg;
    }

    public function destroy(MediaLibrary $mediaLibrary)
    {
        $attributes = $mediaLibrary->getAttributes();
        $paths = array_values(array_filter(
            [$attributes['file_path'] ?? null, $attributes['thumbnail_path'] ?? null],
            fn ($path) => is_string($path) && $path !== '',
        ));

        $mediaLibrary->delete();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        return response()->noContent();
    }
}
