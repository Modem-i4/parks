<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Species;

class FamilySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'tree' => [
                [
                    'name_ukr' => 'Букові',
                    'name_lat' => 'Fagaceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Дуб',
                            'name_lat' => 'Quercus',
                            'species' => [
                                ['name_ukr' => 'Дуб черешчатий', 'name_lat' => 'Quercus robur'],
                                ['name_ukr' => 'Дуб червоний', 'name_lat' => 'Quercus rubra'],
                            ]
                        ],
                        [
                            'name_ukr' => 'Бук',
                            'name_lat' => 'Fagus',
                            'species' => [
                                ['name_ukr' => 'Бук лісовий', 'name_lat' => 'Fagus sylvatica'],
                            ]
                        ],
                    ]
                ],
                [
                    'name_ukr' => 'Кленові',
                    'name_lat' => 'Aceraceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Клен',
                            'name_lat' => 'Acer',
                            'species' => [
                                ['name_ukr' => 'Клен гостролистий', 'name_lat' => 'Acer platanoides'],
                                ['name_ukr' => 'Клен польовий', 'name_lat' => 'Acer campestre'],
                            ]
                        ]
                    ]
                ]
            ],
            'bush' => [
                [
                    'name_ukr' => 'Жимолостеві',
                    'name_lat' => 'Caprifoliaceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Жимолость',
                            'name_lat' => 'Lonicera',
                            'species' => [
                                ['name_ukr' => 'Жимолость звичайна', 'name_lat' => 'Lonicera xylosteum'],
                            ]
                        ]
                    ]
                ],
                [
                    'name_ukr' => 'Трояндові',
                    'name_lat' => 'Rosaceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Шипшина',
                            'name_lat' => 'Rosa',
                            'species' => [
                                ['name_ukr' => 'Шипшина собача', 'name_lat' => 'Rosa canina'],
                            ]
                        ]
                    ]
                ]
            ],
            'hedge' => [
                [
                    'name_ukr' => 'Бобові',
                    'name_lat' => 'Fabaceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Карагана',
                            'name_lat' => 'Caragana',
                            'species' => [
                                ['name_ukr' => 'Карагана деревоподібна', 'name_lat' => 'Caragana arborescens'],
                            ]
                        ]
                    ]
                ]
            ],
            'flower' => [
                [
                    'name_ukr' => 'Айстрові',
                    'name_lat' => 'Asteraceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Айстра',
                            'name_lat' => 'Aster',
                            'species' => [
                                ['name_ukr' => 'Айстра новобельгійська', 'name_lat' => 'Aster novi-belgii'],
                            ]
                        ]
                    ]
                ],
                [
                    'name_ukr' => 'Лілійні',
                    'name_lat' => 'Liliaceae',
                    'genera' => [
                        [
                            'name_ukr' => 'Лілія',
                            'name_lat' => 'Lilium',
                            'species' => [
                                ['name_ukr' => 'Лілія тигрова', 'name_lat' => 'Lilium lancifolium'],
                            ]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($data as $type => $families) {
            foreach ($families as $familyData) {
                $family = Family::create([
                    'name_ukr' => $familyData['name_ukr'],
                    'name_lat' => $familyData['name_lat'],
                    'type' => $type,
                ]);

                foreach ($familyData['genera'] as $genusData) {
                    $genus = Genus::create([
                        'family_id' => $family->id,
                        'name_ukr' => $genusData['name_ukr'],
                        'name_lat' => $genusData['name_lat'],
                    ]);

                    foreach ($genusData['species'] as $speciesData) {
                        Species::create([
                            'genus_id' => $genus->id,
                            'name_ukr' => $speciesData['name_ukr'],
                            'name_lat' => $speciesData['name_lat'],
                        ]);
                    }
                }
            }
        }
    }
}
