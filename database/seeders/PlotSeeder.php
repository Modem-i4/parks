<?php

namespace Database\Seeders;

use App\Models\Plot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 3) as $parkId) {
            foreach (range(1, 6) as $i) {
                Plot::create([
                    'park_id' => $parkId,
                    'name' => "Виділ {$i}"
                ]);
            }
        }
    }
}
