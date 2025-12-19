<?php

namespace Database\Seeders\DemoSeeders;

use Database\Seeders\CoreSeeders\MediaSeeder;
use Illuminate\Database\Seeder;

class MediaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $extraMedia = [
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => '/img/parks/default/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => '/img/parks/default/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => '/img/parks/default/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ],

            
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => '/img/parks/default/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => '/img/parks/default/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => '/img/parks/default/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ],

            
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => '/img/parks/default/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => '/img/parks/default/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => '/img/parks/default/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ]
        ];

        app(MediaSeeder::class)->seedMedia($extraMedia);
    }
}