<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $standardMedia = [
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => 'img/images/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => 'img/images/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '1',
                'file_path' => 'img/images/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ],

            
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => 'img/images/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => 'img/images/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '2',
                'file_path' => 'img/images/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ],

            
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => 'img/images/park1-1.jpg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => 'img/images/park1-2.jpg',
                'description' => 'default description',
                'order' => '1',
                'type' => 'image'
            ],
            [
                'model_type' => 'App\Models\Park',
                'model_id' => '3',
                'file_path' => 'img/images/park1-3.jpg',
                'description' => 'default description',
                'order' => '2',
                'type' => 'image'
            ],
        ];

        foreach ($standardMedia as $media) {
            DB::table('media')->updateOrInsert(
                [
                    'model_type' => $media['model_type'],
                    'model_id' => $media['model_id'],
                    'order' => $media['order'],
                    'type' => $media['type']
                ],
                [
                    'file_path' => $media['file_path'],
                    'description' => $media['description']
                ]
            );
        }
    }
}
