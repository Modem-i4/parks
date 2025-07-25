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
            'selectedMarker' => $park
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

    public function media($id)
    {
        return Park::with('media')->findOrFail($id);
    }

}
