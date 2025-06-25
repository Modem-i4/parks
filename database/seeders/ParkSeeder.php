<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkSeeder extends Seeder
{
    public function run(): void
    {
        $parksGeoJSON = require database_path('data/ParksGeoJSON.php');
        $standardParks = [
            [
                'name' => 'Парк Хоткевича',
                'address' => 'вул. Молодіжна',
                'area' => '55',
                'description' => 'Парк Хоткевича розташований у центрі міста і є популярним місцем для відпочинку та прогулянок. Тут можна знайти численні алеї, дитячі майданчики та зони для пікніків.',
                'geo_json' => $parksGeoJSON['hotkevych_park'],
            ],
            [
                'name' => 'Парк воїнів-визволителів',
                'address' => 'Адреса',
                'area' => '65',
                'description' => 'Парк воїнів-визволителів розташований на півдні міста і є місцем пам\'яті та відпочинку. Тут встановлено численні пам\'ятники та меморіали, а також облаштовані зони для прогулянок і відпочинку.',
                'geo_json' => $parksGeoJSON['liberators_park'],
            ],
            [
                'name' => 'Парк Шевченка',
                'address' => 'Адреса 2',
                'area' => '655',
                'description' => 'Парк Шевченка розташований на заході міста і є одним з найбільших парків у регіоні. Тут можна знайти численні алеї, спортивні майданчики та зони для відпочинку. Парк також славиться своєю природною красою та різноманіттям рослинності.',
                'geo_json' => $parksGeoJSON['shevchenko_park'],
            ],
        ];

        foreach ($standardParks as $park) {
            DB::table('parks')->updateOrInsert(
                [
                    'name' => $park['name']
                ],
                [
                    'address' => $park['address'],
                    'area' => $park['area'],
                    'description' => $park['description'],
                    'geo_json' => json_encode($park['geo_json']),
                ]
            );
        }
    }
}
