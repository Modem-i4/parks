<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function index(Request $request) {
        $parkId = $request->parkId;
        if($parkId) {
            return Plot::where('park_id', $parkId)->get();
        }
        return Plot::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'park_id' => ['required', 'integer', 'exists:parks,id'],
        ]);

        return Plot::create($validated);
    }

    public function update(Request $request, $id)
    {
        $plot = Plot::findOrFail($id);
        $plot->update($request->validate([
            'name' => 'required',
        ]));

        return $plot;
    }

    public function destroy($id)
    {
        $plot = Plot::findOrFail($id);
        $plot->delete();

        return response()->noContent();
    }
}
