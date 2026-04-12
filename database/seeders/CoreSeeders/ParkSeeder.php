<?php

namespace Database\Seeders\CoreSeeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkSeeder extends Seeder
{
    public function run(): void
    {
        $parksGeoJSON = require database_path('data/ParksGeoJSON.php');
        $standardParks = [
            [
                'name' => 'Парк ім. Т.Г. Шевченка',
                'slug' => 'shevchenko_park',
                'address' => 'Адреса 2',
                'area' => '24.4',
                'operator' => 'Компанія 3',
                'description' => 'Улюблений парк містян, де можна відпочити і дітям, і дорослим. Парк імені Тараса Григоровича Шевченка розташований між вулицями Гетьмана Мазепи та Чорновола.',
                'geo_json' => $parksGeoJSON['shevchenko_park'],
            ],
            [
                'name' => 'Парк воїнів-афганців',
                'slug' => 'liberators_park',
                'address' => 'Адреса',
                'area' => '3.9',
                'operator' => 'Компанія 2',
                'description' => 'Парк воїнів-афганців у Франківську знаходиться на перетині вулиць Галицької та Василіянок. Ним часто проходять у справах мешканці або ж гуляють з дітьми чи собаками.',
                'geo_json' => $parksGeoJSON['liberators_park'],
            ],
            [
                'name' => 'Парк по вул. Молодіжна',
                'slug' => 'hotkevych_park',
                'address' => 'вул. Молодіжна',
                'area' => '5.3',
                'operator' => 'Компанія 1',
                'description' => 'Парк знаходиться на вулиці Молодіжній, що поблизу вулиць Івасюка та Хоткевича. Там затишно і спокійно, й можна гарно провести час.',
                'geo_json' => $parksGeoJSON['hotkevych_park'],
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
