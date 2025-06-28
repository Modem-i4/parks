<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Park;

class MarkerSeeder extends Seeder
{
    public function run()
    {
        $parksGeo = include database_path('data/ParksGeoJSON.php');

        $parks = Park::all();

        $markers = [];
        foreach ($parks as $park) {
            $geojson = $parksGeo[$park->slug ?? ''] ?? null;
            if (!$geojson) continue;

            $polygon = $geojson['geometry']['coordinates'][0];

            for ($i = 0; $i < 1000; $i++) {
                $point = $this->generateRandomPointInPolygon($polygon);

                $markers[] = [
                    'park_id' => $park->id,
                    'coordinates' => json_encode($point),
                    'description' => 'descr'
                ];
            }
        }
        DB::table('markers')->insert($markers);
    }

    private function generateRandomPointInPolygon($polygon)
    {
        $minX = $maxX = $polygon[0][0];
        $minY = $maxY = $polygon[0][1];

        foreach ($polygon as $coord) {
            $minX = min($minX, $coord[0]);
            $maxX = max($maxX, $coord[0]);
            $minY = min($minY, $coord[1]);
            $maxY = max($maxY, $coord[1]);
        }

        do {
            $x = $minX + mt_rand() / mt_getrandmax() * ($maxX - $minX);
            $y = $minY + mt_rand() / mt_getrandmax() * ($maxY - $minY);
        } while (!$this->pointInPolygon([$x, $y], $polygon));

        return [$x, $y];
    }

    private function pointInPolygon($point, $polygon)
    {
        $x = $point[0];
        $y = $point[1];

        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi + 0.0000001) + $xi);
            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }
}
