<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InfrastructureTypeSeeder extends Seeder
{
    public function run(): void
    {
        $standardRecommendations = [
            ['name' => 'Туалет'],
            ['name' => 'Кімната матері та дитини'],
            ['name' => 'Дитячий майданчик'],
            ['name' => 'Спортивний майданчик'],
            ['name' => 'Фонтан'],
            ['name' => 'Пам\'ятник'],
        ];

        foreach ($standardRecommendations as $recommendation) {
            DB::table('infrastructure_type')->updateOrInsert(
                ['name' => $recommendation['name']],
                ['custom' => false]
            );
        }
    }
}
