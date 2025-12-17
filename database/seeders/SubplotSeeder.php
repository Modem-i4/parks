<?php

namespace Database\Seeders;

use App\Models\Subplot;
use App\Models\Plot;
use Illuminate\Database\Seeder;

class SubplotSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Plot::query()->select('id')->get() as $plot) {
            foreach (range(1, 3) as $i) {
                Subplot::create([
                    'plot_id' => $plot->id,
                    'name' => "Виділ {$i}",
                ]);
            }
        }
    }
}
