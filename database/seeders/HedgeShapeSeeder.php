<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HedgeShapeSeeder extends Seeder
{
    public function run(): void
    {
        $standardShapes = [
            ['name' => 'Формовані'],
            ['name' => 'Неформовані']
        ];

        foreach ($standardShapes as $standardShape) {
            DB::table('hedge_shapes')->updateOrInsert(
                ['name' => $standardShape['name']]
            );
        }
    }
}
