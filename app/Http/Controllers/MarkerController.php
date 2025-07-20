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
            'green.species',
            'green.tree',
            'green.bush',
            'green.hedge',
            'green.flower',
            'infrastructure',
            'tags:id,name,public,custom,type',
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
        $marker = Marker::with([
            'media.mediaFile',
            'infrastructure.infrastructureType.media.mediaFile',
            'green.species.media.mediaFile',
            'green.species.genus.media.mediaFile',
            'green.species.genus.family.media.mediaFile'
        ])->findOrFail($id);

        if ($marker->media->isNotEmpty()) {
            return $marker;
        }

        if ($marker->type === 'infrastructure') {
            $fallback = $marker->infrastructure->infrastructureType?->media ?? collect();
        } else {
            $fallback =
                $marker->green->species?->media->isNotEmpty() ? $marker->green->species->media :
                ($marker->green->species?->genus?->media->isNotEmpty() ? $marker->green->species->genus->media :
                ($marker->green->species?->genus?->family?->media ?? collect()));
        }

        $marker->setRelation('media', $fallback);

        return $marker;
    }
}
