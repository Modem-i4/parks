<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\News;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RecommendationsTableSeeder::class,
            TagsSeeder::class,
            FamilySeeder::class,
            ParkSeeder::class,
            MediaSeeder::class,
            InfrastructureTypeSeeder::class,
            HedgeRowSeeder::class,
            HedgeShapeSeeder::class,
            PlotSeeder::class,
            MarkerSeeder::class,
            MarkersTagSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@a',
            'role' => UserRole::SUPER_ADMIN
        ]);

        User::factory(10)->create();

        News::factory(10)->create();

    }
}
