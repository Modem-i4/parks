<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Park;
use Illuminate\Http\Request;
use App\Http\Services\MarkerFilters\MarkerFilterConfigService;
use App\Http\Services\MarkerFilters\MarkerFilterService;
use App\Http\Services\UpdateMarkerService;
use App\Http\Services\ValidateMarkerService;
use Illuminate\Validation\ValidationException;

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

    const markerFields = [
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
    ];

    public function getSingleMarker($id)
    {
        $marker = Marker::with(self::markerFields)->findOrFail($id);
        return response()->json($marker);
    }

    public function getSingleMarkerByInv($inv) {
        $marker = Marker::with(['park', ...self::markerFields])->whereHas('green', fn($q) => $q->where('inventory_number', $inv))
            ->first();
        return $marker;
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
        $source = null;

        if ($marker->media->isNotEmpty()) {
            $media = $marker->media;
            $source = 'marker';
        } elseif ($marker->type === 'infrastructure') {
            $media = $marker->infrastructure->infrastructureType?->media ?? collect();
            $source = 'infrastructure_type';
        } elseif ($marker->green) {
            if ($marker->green->species?->media->isNotEmpty()) {
                $media = $marker->green->species->media;
                $source = 'species';
            } elseif ($marker->green->species?->genus?->media->isNotEmpty()) {
                $media = $marker->green->species->genus->media;
                $source = 'genus';
            } elseif ($marker->green->species?->genus?->family?->media) {
                $media = $marker->green->species->genus->family->media;
                $source = 'family';
            }
        }

        return response()->json([
            'media' => $media,
            'source' => $source,
        ]);
    }


    public function update(Request $request, $id)
    {
        $marker = Marker::findOrFail($id);
        try {
            $data = app(ValidateMarkerService::class)->validate($request->all());
            app(UpdateMarkerService::class)->handle($marker, $data);
            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Failed to update marker'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = app(ValidateMarkerService::class)->validate($request->all());
            $marker = new Marker();
            app(UpdateMarkerService::class)->handle($marker, $data);
            return response()->json(['success' => true, 'id' => $marker->id]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            throw($e); 
            return response()->json(['error' => 'Failed to create marker'], 500);
        }
    }

}
