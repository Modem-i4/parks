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

        'green.subplot.plot',
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
            ->whereHas('green', fn ($q) => $q
                ->where('inventory_number', $inv)
                ->orWhere('inventory_number_old', $inv))
            ->first();
    }

    public function findByInventoryOrTag(string $number): ?Marker
    {
        $number = trim($number);
        $inventoryTag = $this->normalizeInventoryTag($number);

        return Marker::with(self::RELATIONS)
            ->where(function ($query) use ($number, $inventoryTag) {
                $query->whereHas(
                    'green',
                    fn ($greenQuery) => $greenQuery
                        ->where('inventory_number', $number)
                        ->orWhere('inventory_number_old', $number)
                );

                if ($inventoryTag !== null) {
                    $query->orWhereHas(
                        'green.tree',
                        fn ($treeQuery) => $treeQuery->where('inventory_tag', $inventoryTag)
                    );
                }
            })
            ->first();
    }

    private function normalizeInventoryTag(string $number): ?string
    {
        $number = mb_strtoupper(trim($number));

        if (! preg_match('/^([АМШ])[\s-]*0*(\d+)$/u', $number, $matches)) {
            return null;
        }

        $width = $matches[1] === 'Ш' ? 6 : 5;

        return $matches[1].str_pad($matches[2], $width, '0', STR_PAD_LEFT);
    }

    public function ensureBelongsToPark(Marker $marker, Park $park): Marker
    {
        if ($marker->park_id !== $park->id) {
            abort(404);
        }

        return $marker;
    }
}
