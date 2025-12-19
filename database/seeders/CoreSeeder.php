<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\CoreSeeders\{
    RecommendationsSeeder,
    ParkSeeder,
    MediaSeeder,
    InfrastructureTypeSeeder,
    HedgeRowSeeder,
    HedgeShapeSeeder,
    TagsSeeder,
};
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RecommendationsSeeder::class,
            InfrastructureTypeSeeder::class,
            HedgeRowSeeder::class,
            HedgeShapeSeeder::class,
            TagsSeeder::class,
            ParkSeeder::class,
            MediaSeeder::class
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@parks.if.ua',
            'role' => UserRole::SUPER_ADMIN
        ]);
    }
}
