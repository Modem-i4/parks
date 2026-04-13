<?php

namespace Database\Seeders\CoreSeeders;

use App\Models\Park;
use App\Models\Plot;
use App\Models\Subplot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlotSeeder extends Seeder
{
    public function run(): void
    {
        $plots = require database_path('data/Plots.php');

        foreach ($plots as $parkSlug => $parkPlots) {
            $parkId = Park::where('slug', $parkSlug)->firstOrFail()->id;

            foreach ($parkPlots as $plotName => $plotData) {
                Plot::firstOrCreate(
                    [
                        'park_id' => $parkId,
                        'name' => $plotName,
                    ],
                    [
                        'coordinates' => $plotData['coordinates'],
                    ]
                );

                $plotId = DB::table('plots')
                    ->where('park_id', $parkId)
                    ->where('name', $plotName)
                    ->value('id');

                foreach ($plotData['subplots'] as $subplotName => $subplotCoords) {
                    Subplot::firstOrCreate(
                        [
                            'plot_id' => $plotId,
                            'name' => $subplotName,
                        ],
                        [
                            'coordinates' => $subplotCoords,
                        ]
                    );
                }
            }
        }
    }
}
