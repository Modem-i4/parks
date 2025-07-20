<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Park;
use Illuminate\Http\Request;
use App\Http\Services\MarkerFilters\MarkerFilterConfigService;
use App\Http\Services\MarkerFilters\MarkerFilterService;

class MarkerController extends Controller
{
    protected $filterService;
    protected $filterConfigService;

    public function __construct(MarkerFilterConfigService $filterConfigService, MarkerFilterService $filterService)
    {
        $this->filterConfigService = $filterConfigService;
        $this->filterService = $filterService;
    }

    public function getFilters(Request $request) {
        $mode = $request->query('mode', 'infrastructure');
        $config =  $this->filterConfigService->get($mode);
        return response()->json($config);
    }

    public function getSingleMarker($id)
    {
        $marker = Marker::with([
            'icon',
            'green.tree',
            'green.bush',
            'green.hedge',
            'green.flower',
            'infrastructure',
            'tags:id,name,public,custom,type',

            'infrastructure.infrastructureType:id,name,description',
            'green.species:id,genus_id,name_ukr,name_lat',
            'green.species.genus:id,family_id,name_ukr,name_lat',
            'green.species.genus.family:id,name_ukr,name_lat',
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
        return $markers;
    }

    public function media($id)
    {
        $marker = Marker::findOrFail($id);
        $media = collect();
        if ($marker->media->isNotEmpty()) {
            $media = $marker->media;
        } elseif ($marker->type === 'infrastructure') {
            $media = $marker->infrastructure->infrastructureType?->media ?? collect();
        } else {
            $media =
                $marker->green->species?->media->isNotEmpty()
                    ? $marker->green->species->media
                    : (
                        $marker->green->species?->genus?->media->isNotEmpty()
                            ? $marker->green->species->genus->media
                            : ($marker->green->species?->genus?->family?->media ?? collect())
                    );
        }

        return response()->json(['media' => $media]);
    }
}
