<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Park;
use Illuminate\Http\Request;
use App\Http\Services\MarkerFilterService;

class MarkerController extends Controller
{
    protected $filterService;

    public function __construct(MarkerFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function getFilters() {
        $config =  $this->filterService->getFiltersConfig();
        return response()->json($config);
    }

    public function getSingleMarker($id)
    {
        $marker = Marker::with([
            'icon',
            'green.species',
            'green.tree',
            'green.bush',
            'green.hedge',
            'green.flower',
            'infrastructure',
        ])->findOrFail($id);

        if ($marker->green) {
            $detailsOptions = ['tree', 'bush', 'hedge', 'flower'];
            foreach ($detailsOptions as $option) {
                $marker->green->details ??= $marker->green->$option;
                unset($marker->green->$option);
            }
        }
        return response()->json($marker);
    }


    public function filterParkMarkers(Request $request, $id)
    {
        $filters = $request->input('filters');
        $markers = $this->filterService->filter($id, $filters);

        $park = Park::with('icon')->findOrFail($id);
        $center = $park->coordinates;
        if (!$center) {
            return $markers;
        }
        
        $markers = $this->addDistanceToCenter($markers, $center);
        return $markers->sortBy('distanceToCenter')->values();
    }

    protected function addDistanceToCenter($markers, array $center)
    {
        [$centerLng, $centerLat] = $center;

        $calculateDistance = function ($lng1, $lat1, $lng2, $lat2) {
            $dx = $lng2 - $lng1;
            $dy = $lat2 - $lat1;
            return sqrt($dx * $dx + $dy * $dy);
        };

        $markers->each(function ($marker) use ($calculateDistance, $centerLng, $centerLat) {
            if (!empty($marker->coordinates) && count($marker->coordinates) === 2) {
                [$markerLng, $markerLat] = $marker->coordinates;
                $marker->distanceToCenter = $calculateDistance($centerLng, $centerLat, $markerLng, $markerLat);
            } else {
                $marker->distanceToCenter = INF;
            }
        });

        return $markers;
    }
}
