<?php

namespace App\Http\Controllers;

use App\Models\Park;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkController extends Controller
{
    public function index()
    {
        return Inertia::render('Parks', [
            'parks' => $this->getAllParks(),
        ]);
    }

    protected function getAllParks()
    {
        return Park::with('icon')->select('id', 'name', 'address', 'area', 'description', 'geo_json')->get();
    }
}
