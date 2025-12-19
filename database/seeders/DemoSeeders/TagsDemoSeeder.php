<?php

namespace Database\Seeders\DemoSeeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Database\Seeders\CoreSeeders\TagsSeeder;

class TagsDemoSeeder extends Seeder
{
    public function run()
    {
        $extraTags = [
            ['name' => 'Інфраструктурний', 'public' => true, 'type' => 'infrastructure'],
        ];
        app(TagsSeeder::class)->seedTags($extraTags);

        $types = ['all', 'infrastructure', 'tree', 'bush', 'hedge', 'flower'];
        $this->seedRandomTags($types);
    }
    public function seedRandomTags($types)
    {
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
