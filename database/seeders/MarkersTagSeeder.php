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
        $tags = Tag::all()->groupBy('type');

        $markers = Marker::select('id', 'type')->get();

        $inserts = [];

        foreach ($markers as $marker) {
            $typeTags = $tags[$marker->type] ?? collect();
            $genericTags = $tags['all'] ?? collect();

            $availableTags = $typeTags->merge($genericTags)->shuffle();

            foreach ($availableTags->take(rand(2, 3)) as $tag) {
                $inserts[] = [
                    'marker_id' => $marker->id,
                    'tag_id' => $tag->id,
                ];
            }
        }

        DB::table('markers_tags')->insertOrIgnore($inserts);
    }
}
