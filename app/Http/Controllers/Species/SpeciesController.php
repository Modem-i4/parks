<?php
namespace App\Http\Controllers\Species;

use App\Http\Controllers\Controller;
use App\Models\Species;
use Illuminate\Http\Request;

class SpeciesController extends Controller
{
    public function index($type)
    {
        return Species::whereHas('genus.family', function ($query) use ($type) {
            $query->where('type', $type);
        })->get();
    }

    public function byGenus($type, $genus_id)
    {
        return Species::where('type', $type)
                      ->where('genus_id', $genus_id)
                      ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'genus_id' => 'required|exists:genus,id',
            'name_ukr' => 'required|string',
            'name_lat' => 'required|string',
        ]);

        return Species::create($validated);
    }

    public function update(Request $request, $id)
    {
        $species = Species::findOrFail($id);
        $species->update($request->validate([
            'name_ukr' => 'required|string',
            'name_lat' => 'required|string',
        ]));

        return $species;
    }

    public function destroy($id)
    {
        $species = Species::findOrFail($id);
        $species->delete();

        return response()->noContent();
    }
}
