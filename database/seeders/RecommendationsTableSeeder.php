<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationsTableSeeder extends Seeder
{
    public function run()
    {
        $standardRecommendations = [
            ['name' => 'видалення'],
            ['name' => 'обстеження'],
            ['name' => 'лікування'],
            ['name' => 'санітарна обрізка'],
            ['name' => 'формуюча обрізка'],
            ['name' => 'встановлення огорожі'],
        ];

        foreach ($standardRecommendations as $recommendation) {
            DB::table('recommendations')->updateOrInsert(
                ['name' => $recommendation['name']],
                ['custom' => false]
            );
        }
    }
}
