<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Marker;
use App\Models\Tag;

class MarkersTagSeeder extends Seeder
{
    public function run()
    {
        $markers = Marker::all();
        $allTags = Tag::where('type', 'all')->get()->keyBy('id');

        foreach ($markers as $marker) {
            $typeTags = Tag::where('type', $marker->type)->get()->keyBy('id');

            $availableTags = $typeTags->merge($allTags)->shuffle();

            $selectedTags = $availableTags->take(rand(2, 3));

            foreach ($selectedTags as $tag) {
                DB::table('markers_tags')->updateOrInsert(
                    [
                        'marker_id' => $marker->id,
                        'tag_id' => $tag->id,
                    ],
                    []
                );
            }
        }
    }
}
