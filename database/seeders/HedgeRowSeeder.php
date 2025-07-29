<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HedgeRowSeeder extends Seeder
{
    public function run(): void
    {
        $standardRows = [
            ['name' => 'Однорядні'],
            ['name' => 'Дворядні']
        ];

        foreach ($standardRows as $standardRows) {
            DB::table('hedge_rows')->updateOrInsert(
                ['name' => $standardRows['name']]
            );
        }
    }
}
