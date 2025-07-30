<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationsTableSeeder extends Seeder
{
    public function run()
    {
        $standardRecommendations = [
            ['name' => 'Видалення'],
            ['name' => 'Обстеження'],
            ['name' => 'Лікування'],
            ['name' => 'Санітарна обрізка'],
            ['name' => 'Формуюча обрізка'],
            ['name' => 'Встановлення огорожі'],
            ['name' => 'Полив'],
            ['name' => 'Підживлення'],
        ];

        foreach ($standardRecommendations as $recommendation) {
            DB::table('recommendations')->updateOrInsert(
                ['name' => $recommendation['name']],
                ['custom' => false]
            );
        }
    }
}
