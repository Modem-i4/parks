<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsTableSeeder extends Seeder
{
    public function run()
    {
        $standardTags = [
            ['name' => 'Гриби', 'public' => true, 'type' => 'tree'],
            ['name' => 'Трухлявість', 'public' => true, 'type' => 'tree'],
            ['name' => 'Дупла', 'public' => true, 'type' => 'tree'],
            ['name' => 'Морозобоїни', 'public' => true, 'type' => 'tree'],
            ['name' => 'Цвяхи', 'public' => true, 'type' => 'tree'],
            ['name' => 'Лампи та дроти', 'public' => true, 'type' => 'tree'],
            ['name' => 'Пошкодження кори', 'public' => true, 'type' => 'tree'],
            ['name' => 'Розвилки', 'public' => true, 'type' => 'tree'],
        ];

        foreach ($standardTags as $tag) {
            DB::table('tags')->updateOrInsert(
                ['name' => $tag['name'], 'type' => $tag['type']],
                ['public' => $tag['public'], 'custom' => false],
            );
        }
    }
}