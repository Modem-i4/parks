<?php

namespace Database\Seeders\CoreSeeders;

use App\Models\Park;
use App\Models\Plot;
use App\Models\Subplot;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    public function run(): void
    {
        $data = include database_path('data/Plots.php');

        foreach ($data as $parkSlug => $subplotsToPlots) {
            $park = Park::where('slug', $parkSlug)->firstOrFail();

            foreach ($subplotsToPlots as $subplotName => $plotNames) {
                foreach ($plotNames as $plotName) {
                    $plot = Plot::create([
                        'park_id' => $park->id,
                        'name' => (string) $plotName,
                    ]);

                    Subplot::create([
                        'plot_id' => $plot->id,
                        'name' => (string) $subplotName,
                    ]);
                }
            }
        }
    }
}
