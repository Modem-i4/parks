<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\DemoSeeders\{
    FamilyDemoSeeder,
    MarkerDemoSeeder,
    MarkersDemoTagSeeder,
    MediaDemoSeeder,
};


class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,

            FamilyDemoSeeder::class,
            MediaDemoSeeder::class,
            MarkerDemoSeeder::class,
            MarkersDemoTagSeeder::class,
        ]);

        User::factory(10)->create();

        News::factory(10)->create();
    }
}
