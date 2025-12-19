<?php
namespace Database\Seeders\BasicSeeders;

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
            ]
        ];

        $this->seedTaxonomy($data);
    }

    public function seedTaxonomy($data) {
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
