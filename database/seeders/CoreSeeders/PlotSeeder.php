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

        foreach ($data as $parkSlug => $plotsToSubplots) {
            $park = Park::where('slug', $parkSlug)->firstOrFail();

            foreach ($plotsToSubplots as $plotName => $subplotNames) {
                $plot = Plot::firstOrCreate([
                    'park_id' => $park->id,
                    'name' => (string) $plotName,
                ]);

                foreach ($subplotNames as $subplotName) {
                    Subplot::firstOrCreate([
                        'plot_id' => $plot->id,
                        'name' => (string) $subplotName,
                    ]);
                }
            }
        }
    }
}
