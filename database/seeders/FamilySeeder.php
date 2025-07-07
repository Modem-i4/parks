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
        $familiesData = [
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
        ];

        foreach ($familiesData as $familyData) {
            $family = Family::create([
                'name_ukr' => $familyData['name_ukr'],
                'name_lat' => $familyData['name_lat'],
                'type' => 'tree',
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
