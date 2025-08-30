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
                'slug' => 'hotkevych_park',
                'address' => 'вул. Молодіжна',
                'area' => '55',
                'operator' => 'Компанія 1',
                'description' => 'Парк Хоткевича розташований у центрі міста і є популярним місцем для відпочинку та прогулянок. Тут можна знайти численні алеї, дитячі майданчики та зони для пікніків.',
                'geo_json' => $parksGeoJSON['hotkevych_park'],
            ],
            [
                'name' => 'Парк воїнів-визволителів',
                'slug' => 'liberators_park',
                'address' => 'Адреса',
                'area' => '65',
                'operator' => 'Компанія 2',
                'description' => 'Парк воїнів-визволителів розташований на півдні міста і є місцем пам\'яті та відпочинку. Тут встановлено численні пам\'ятники та меморіали, а також облаштовані зони для прогулянок і відпочинку.',
                'geo_json' => $parksGeoJSON['liberators_park'],
            ],
            [
                'name' => 'Парк Шевченка',
                'slug' => 'shevchenko_park',
                'address' => 'Адреса 2',
                'area' => '655',
                'operator' => 'Компанія 3',
                'description' => 'Парк Шевченка розташований на заході міста і є одним з найбільших парків у регіоні. Тут можна знайти численні алеї, спортивні майданчики та зони для відпочинку. Парк також славиться своєю природною красою та різноманіттям рослинності.',
                'geo_json' => $parksGeoJSON['shevchenko_park'],
            ],
        ];

        foreach ($standardParks as $park) {
            DB::table('parks')->updateOrInsert(
                [
                    'slug' => $park['slug']
                ],
                [
                    'name' => $park['name'],
                    'address' => $park['address'],
                    'area' => $park['area'],
                    'operator' => $park['operator'],
                    'description' => $park['description'],
                    'geo_json' => json_encode($park['geo_json']),
                ]
            );
        }
    }
}
