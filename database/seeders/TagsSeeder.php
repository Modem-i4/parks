<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;

class TagsSeeder extends Seeder
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
            ['name' => 'Інфраструктурний', 'public' => true, 'type' => 'infrastructure'],
        ];

        foreach ($standardTags as $tag) {
            DB::table('tags')->updateOrInsert(
                ['name' => $tag['name'], 'type' => $tag['type']],
                ['public' => $tag['public'], 'custom' => false],
            );
        }

        $types = ['all', 'infrastructure', 'tree', 'bush', 'hedge', 'flower'];

        foreach (range(1, 10) as $i) {
            Tag::create([
                'name' => 'Custom Tag ' . $i,
                'type' => $types[array_rand($types)],
                'public' => (bool)random_int(0, 1),
                'custom' => true,
            ]);
        }
    }
}
