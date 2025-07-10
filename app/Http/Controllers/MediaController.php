<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with('mediaFile')->orderBy('order');

        if ($request->filled(['model_type', 'model_id'])) {
            $query->where('model_type', $request->model_type)
                  ->where('model_id', $request->model_id);
        }

        if ($request->filled('type')) {
            $query->whereHas('mediaFile', fn ($q) =>
                $q->where('type', $request->type)
            );
        }

        return response()->json($query->get());
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'type' => 'required|string', 
            'media' => 'required|array',
            'media.*.media_library_id' => 'required|integer',
            'media.*.order' => 'nullable|integer',
            'media.*.description' => 'nullable|string',
        ]);

        $existing = Media::where('model_type', $data['model_type'])
            ->where('model_id', $data['model_id'])
            ->whereHas('mediaFile', function ($query) use ($data) {
                $query->where('type', $data['type']);
            })->get();

        $newMedia = collect($data['media']);

        $toDelete = $existing->filter(fn($item) =>
            !$newMedia->contains('media_library_id', $item->media_library_id)
        );
        Media::destroy($toDelete->pluck('id'));

        foreach ($newMedia as $index => $item) {
            Media::updateOrCreate(
                [
                    'model_type' => $data['model_type'],
                    'model_id' => $data['model_id'],
                    'media_library_id' => $item['media_library_id'],
                ],
                [
                    'order' => $item['order'] ?? $index,
                    'description' => $item['description'] ?? '',
                ]
            );
        }

        return response()->json(['status' => 'ok']);
    }
}
