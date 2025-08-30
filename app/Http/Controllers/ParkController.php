<?php

namespace App\Http\Controllers;

use App\Models\Park;
use App\Http\Services\MarkerService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkController extends Controller
{
    // Park pages
    public function index()
    {
        return $this->renderPage(false, null);
    }

    public function show($parkId)
    {
        return $this->renderPage(false, $parkId);
    }

    public function showSinglePark($parkId)
    {
        return $this->renderPage(true, $parkId);
    }

    private function renderPage(bool $isSingleParkView, $parkId = null)
    {
        $park = $parkId ? Park::with('icon')->findOrFail($parkId) : null;

        return Inertia::render('Parks', [
            'isSingleParkView' => $isSingleParkView && $park,
            'selectedPark' => $park,
            'showFooter' => false,
        ]);
    }
    // Marker park pages
    public function showSingleMarker($parkId, $markerId, MarkerService $service) {
        $park = $parkId ? Park::findOrFail($parkId) : null;
        $marker = $markerId ? $service->findById($markerId) : null;
        $service->ensureBelongsToPark($marker, $park);
        return $this->renderMarkerPage($marker, $park);
    }

    public function parksMarkerIndex($inv, MarkerService $service) 
    {
        $marker = $service->findByInventory($inv);
        return $this->renderMarkerPage($marker, $marker->park);
    }

    private function renderMarkerPage($marker, $park) {
        if(!$marker || !$park) {
            return redirect()->route('parks');
        }
        return Inertia::render('Parks', [
            'isSingleParkView' => true,
            'selectedMarker' => $marker,
            'selectedPark' => $park,
            'showFooter' => false
        ]);
    }

    public function getPark($id)
    {
        return Park::with('icon')
            ->select('id', 'name', 'address', 'area', 'description', 'geo_json')
            ->where('id', $id)
            ->first();
    }

    public function getParksList()
    {
        return Park::with('icon')
            ->select('id', 'name', 'address', 'area', 'description', 'geo_json')
            ->orderByDesc('area')
            ->get();
    }
    public function update(Request $request, $id)
    {
        try {
            $park = Park::findOrFail($id);
            $park->update($request->validate([
                'name' => ['sometimes','required','string'],
                'address' => ['sometimes','required','string'],
                'area' => ['sometimes','required','integer'],
                'description' => ['sometimes','required','string'],
            ]));
            return $park;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Failed to update park'], 500);
        }
    }

    public function media($id)
    {
        $park = Park::with('media.mediaFile')->findOrFail($id);

        return response()->json(['media' => $park->media]);
    }

}
