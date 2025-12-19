<?php

namespace Database\Seeders;

use Database\Seeders\BasicSeeders\{
    FamilySeeder,
};
use Illuminate\Database\Seeder;

class BasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,

            FamilySeeder::class,
        ]);
    }
}