<?php

namespace App\Http\Services\Report;

use App\Http\Services\MarkerService;
use App\Models\Marker;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MarkerReportDataService
{
    public function getMarkers(array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(Arr::wrap($ids))));
        if (empty($ids)) {
            return collect();
        }

        $order = array_flip($ids);

        return Marker::with(array_merge(MarkerService::RELATIONS, ['park.icon']))
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Marker $marker) => $order[$marker->id] ?? PHP_INT_MAX)
            ->values();
    }
}
