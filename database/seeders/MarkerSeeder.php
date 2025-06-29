<?php

namespace Database\Seeders;

use App\Enums\QualityState;
use App\Models\Marker;
use App\Models\Green;
use App\Models\Tree;
use App\Models\Bush;
use App\Models\Hedge;
use App\Models\Flower;
use App\Models\Infrastructure;
use App\Models\Park;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Enums\TagType;

class MarkerSeeder extends Seeder
{
    public function run()
    {
        $parksGeo = include database_path('data/ParksGeoJSON.php');

        $parks = Park::all();

        foreach ($parks as $park) {
            $geojson = $parksGeo[$park->slug ?? ''] ?? null;
            if (!$geojson) continue;

            $polygon = $geojson['geometry']['coordinates'][0];
            $types = TagType::values();

            for ($i = 0; $i < 100; $i++) { 
                $point = $this->generateRandomPointInPolygon($polygon);
                $type = $types[array_rand($types)];
                $marker = Marker::create([
                    'park_id' => $park->id,
                    'plot_id' => null,
                    'coordinates' => $point,
                    'description' => 'descr',
                    'type' => $type
                ]);
                
                if ($type !== 'infrastructure') {
                    $qualityStates = QualityState::values();
                    $green = Green::create([
                        'id' => $marker->id,
                        'inventory_number' => 'INV-' . $i,
                        'species_id' => null,
                        'planting_date' => now()->subYears(rand(1, 20)),
                        'quality_state' => $qualityStates[array_rand($qualityStates)],
                        'quality_state_note' => 'No issues',
                    ]);

                    $subtype = ['tree', 'bush', 'hedge', 'flower'][array_rand(['tree', 'bush', 'hedge', 'flower'])];

                    if ($subtype === 'tree') {
                        Tree::create([
                            'id' => $green->id,
                            'height_m' => rand(5, 20),
                            'trunk_diameter_cm' => rand(10, 60),
                            'trunk_circumference_cm' => rand(30, 180),
                            'tilt_degree' => rand(0, 10),
                            'crown_condition_percent' => rand(60, 100),
                        ]);
                    } elseif ($subtype === 'bush') {
                        Bush::create([
                            'id' => $green->id,
                            'quantity' => rand(1, 10),
                        ]);
                    } elseif ($subtype === 'hedge') {
                        Hedge::create([
                            'id' => $green->id,
                            'length_m' => rand(5, 50),
                            'hedge_type_row' => null,
                            'hedge_type_shape' => null,
                        ]);
                    } elseif ($subtype === 'flower') {
                        Flower::create([
                            'id' => $green->id,
                        ]);
                    }
                }
                elseif ($type === 'infrastructure') {
                    Infrastructure::create([
                        'id' => $marker->id,
                        'name' => 'Bench ' . $i,
                    ]);
                }
            }
        }
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
