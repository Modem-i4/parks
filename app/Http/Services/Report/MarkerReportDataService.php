<?php

namespace App\Http\Services\Report;

use App\Models\Marker;
use App\Models\Park;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MarkerReportDataService
{
    private const REPORT_RELATIONS = [
        'park:id,name,geo_json',
        'park.icon',
        'tags:id,name',
        'green:id,inventory_number,species_id,subplot_id,green_state,green_state_changed_at',
        'green.tree:id,height_m,trunk_circumference_cm',
        'green.subplot:id,plot_id,name',
        'green.subplot.plot:id,name',
        'green.species:id,name_ukr',
        'green.works:id,green_id,recommendation_id,recommendation_date,execution_date,notes',
        'green.works.recommendation:id,name',
        'infrastructure:id,name,infrastructure_type_id',
        'infrastructure.infrastructureType:id,name',
    ];

    public function getMarkers(array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(Arr::wrap($ids))));
        if (empty($ids)) {
            return collect();
        }

        $order = array_flip($ids);

        return Marker::query()
            ->select(['id', 'park_id', 'type', 'coordinates'])
            ->with(self::REPORT_RELATIONS)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Marker $marker) => $order[$marker->id] ?? PHP_INT_MAX)
            ->values();
    }

    public function getParks(Collection $markers): Collection
    {
        $parkIds = $markers->pluck('park_id')->filter()->unique()->values();

        if ($parkIds->isEmpty()) {
            return collect();
        }

        return Park::query()
            ->select(['id', 'name', 'geo_json'])
            ->with([
                'plots:id,park_id,name,coordinates',
                'plots.subplots:id,plot_id,name,coordinates',
            ])
            ->whereIn('id', $parkIds)
            ->get();
    }
}
