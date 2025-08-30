<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Park;
use Illuminate\Http\Request;
use App\Http\Services\MarkerFilters\MarkerFilterConfigService;
use App\Http\Services\MarkerFilters\MarkerFilterService;
use App\Http\Services\UpdateMarkerService;
use App\Http\Services\ValidateMarkerService;
use App\Http\Services\Export\ExportService;
use App\Http\Services\Import\ImportService;
use App\Http\Services\MarkerService;
use Illuminate\Validation\ValidationException;

class MarkerController extends Controller
{
    public function __construct(
        protected MarkerFilterConfigService $filterConfigService, 
        protected MarkerFilterService $filterService,
        protected MarkerService $markerService,
        protected ValidateMarkerService $validateMarkerService,
        protected UpdateMarkerService $updateMarkerService
    ) {}

    public function getFilters(Request $request) {
        $mode = $request->query('mode', 'green');
        $config =  $this->filterConfigService->get($mode);
        return response()->json($config);
    }

    public function getSingleMarker($id)
    {
        $marker = $this->markerService->findById($id);
        return response()->json($marker);
    }

    public function getSingleMarkerByInv($inv) {
        $marker = $this->markerService->findByInventory($inv);
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
            $data = $this->validateMarkerService->validate($request->all());
            $this->updateMarkerService->handle($marker, $data);
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
            $data = $this->validateMarkerService->validate($request->all());
            $marker = new Marker();
            $this->updateMarkerService->handle($marker, $data);
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

    public function destroy($id)
    {
        $marker = Marker::findOrFail($id);
        $marker->delete();
        return response()->noContent();
    }

    public function export(Request $request, ExportService $service)
    {
        $ids = $request->input('markers', []);
        $format = $request->input('format', 'geojson');
        return $service->export($ids, $format);
    }

    public function import(Request $request, ImportService $service)
    {
        $request->validate([
            'file' => 'required|file',
            'mode' => 'required|string'
        ]);

        $results = $service->import($request->file('file'), $request->input('mode', 'update'));

        return response()->json([
            'message' => 'Імпорт виконано',
            'results' => $results,
        ]);
    }

    public function preview(Request $request, ImportService $service)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $results = $service->preview($request->file('file'));

        return response()->json([
            'message' => 'Підрахуннок',
            'results' => $results,
        ]);
    }

}
