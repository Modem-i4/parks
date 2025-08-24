<?php

namespace App\Http\Services;

use App\Models\Marker;
use App\Models\Park;

class MarkerService
{
    public const RELATIONS = [
        'icon',
        'green.tree',
        'green.bush',
        'green.hedge',
        'green.flower',
        'infrastructure',
        'tags:id,name,public,custom,type',

        'green.hedge.hedge_row',
        'green.hedge.hedge_shape',

        'green.plot',
        'green.works.recommendation',

        'infrastructure.infrastructureType:id,name,description',
        'green.species:id,genus_id,name_ukr,name_lat',
        'green.species.genus:id,family_id,name_ukr,name_lat',
        'green.species.genus.family:id,name_ukr,name_lat',

        'park',
    ];

    public function findById(int $id): Marker
    {
        return Marker::with(self::RELATIONS)->findOrFail($id);
    }

    public function findByInventory(string $inv): ?Marker
    {
        return Marker::with(self::RELATIONS)
            ->whereHas('green', fn($q) => $q->where('inventory_number', $inv))
            ->first();
    }

    public function ensureBelongsToPark(Marker $marker, Park $park): Marker
    {
        if ($marker->park_id !== $park->id) {
            abort(404);
        }
        return $marker;
    }
}
