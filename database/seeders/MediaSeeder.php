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


            
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '1',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '2',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '3',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '4',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '5',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
            [
                'model_type' => 'App\Models\InfrastructureType',
                'model_id' => '6',
                'file_path' => 'img/icons/tree-park.svg',
                'description' => 'default description',
                'order' => '0',
                'type' => 'icon'
            ],
        ];

        foreach ($standardMedia as $media) {
            $mediaLibraryId = DB::table('media_library')->where('file_path', $media['file_path'])->value('id');

            if (!$mediaLibraryId) {
                $mediaLibraryId = DB::table('media_library')->insertGetId([
                    'file_path' => $media['file_path'],
                    'type' => $media['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('media')->updateOrInsert(
                [
                    'media_library_id' => $mediaLibraryId,
                    'model_type' => $media['model_type'],
                    'model_id' => $media['model_id'],
                    'order' => $media['order'],
                ],
                [
                    'description' => $media['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}