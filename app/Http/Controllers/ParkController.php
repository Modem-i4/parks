<?php

namespace App\Http\Controllers;

use App\Models\Park;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkController extends Controller
{
    /*
    public function index()
    {
        return Inertia::render('Parks/Index', [
            'parks' => $this->getAllParks(),
        ]);
    }
    public function single($id)
    {
        $park = Park::findOrFail($id);
        $tab = request('tab');

        return Inertia::render('Parks/Single', [
            'park' => $park,
            'tab' => $tab,
        ]);
    }
        */

    public function index($id = null)
    {
        $isSingleParkView = $id !== null;
        $park = $id ? Park::find($id) : null;
        return Inertia::render('Parks', [
            'isSingleParkView' => $isSingleParkView,
            'selectedMarker' => $park
        ]);
    }

    public function getParksList()
    {
        return Park::with('icon')
            ->select('id', 'name', 'address', 'area', 'description', 'geo_json')
            ->get()
            ->map(function ($park) {
                $park->coordinates = $park->geo_json['properties']['center'] ?? null;
                return $park;
            });
    }

    public function media($id)
    {
        return Park::with('media')->findOrFail($id);
    }

}
