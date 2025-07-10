<?php
namespace App\Http\Controllers;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use enshrined\svgSanitize\Sanitizer;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'image';
        return MediaLibrary::orderByDesc('created_at')->where('type', $type)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimetypes:image/jpeg,image/png,image/webp,image/bmp,image/gif,image/svg+xml,image/svg',
            'type' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        if (in_array($mime, ['image/svg+xml', 'image/svg'])) {
            $cleanSvg = $this->sanitizeSvg($file);
            $filename = uniqid('svg_', true) . '.svg';
            $path = 'uploads/' . $filename;
            Storage::disk('public')->put($path, $cleanSvg);
        } else {
            $path = $file->store('uploads', 'public');
        }

        $mediaFile = MediaLibrary::create([
            'file_path' => $path,
            'type' => $request->type ?? 'image',
        ]);

        return response()->json($mediaFile, 201);
    }

    public function sanitizeSvg(UploadedFile $file): string
    {
        $svg = file_get_contents($file->getRealPath());

        $sanitizer = new Sanitizer();
        $cleanSvg = $sanitizer->sanitize($svg);

        if ($cleanSvg === false) {
            throw new \Exception('Invalid or uncleanable SVG.');
        }

        return $cleanSvg;
    }

    public function destroy(MediaLibrary $mediaLibrary)
    {
        Storage::disk('public')->delete($mediaLibrary->file_path);
        $mediaLibrary->delete();

        return response()->noContent();
    }
}
