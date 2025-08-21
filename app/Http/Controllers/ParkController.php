<?php

namespace App\Http\Controllers;

use App\Models\Park;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkController extends Controller
{
    public function index($id = null)
    {
        $isSingleParkView = $id !== null;
        $park = $id ? Park::find($id) : null;
        return Inertia::render('Parks', [
            'isSingleParkView' => $isSingleParkView,
            'selectedPark' => $park,
            'showFooter' => false
        ]);
    }

    public function parksMarkerIndex($inv) 
    {
        $marker = app(MarkerController::class)->getSingleMarkerByInv($inv);
        if(!$marker) {
            return redirect()->route('parks');
        }
        return Inertia::render('Parks', [
            'isSingleParkView' => true,
            'selectedMarker' => $marker,
            'selectedPark' => $marker->park,
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
        return Park::with('icon')->select('id', 'name', 'address', 'area', 'description', 'geo_json')->get();
    }

    public function update(Request $request, $id)
    {
        $park = Park::findOrFail($id);
        $park->update($request->validate([
            'name' => 'string',
            'address' => 'string',
            'description' => 'string',
        ]));

        return $park;
    }

    public function media($id)
    {
        $park = Park::with('media.mediaFile')->findOrFail($id);

        return response()->json(['media' => $park->media]);
    }

}
