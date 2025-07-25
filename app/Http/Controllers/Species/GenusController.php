<?php
namespace App\Http\Controllers\Species;

use App\Http\Controllers\Controller;
use App\Models\Genus;
use Illuminate\Http\Request;

class GenusController extends Controller
{
    public function index($type)
    {
        return Genus::whereHas('family', function ($query) use ($type) {
            $query->where('type', $type);
        })->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'name_ukr' => 'required|string',
            'name_lat' => 'required|string',
        ]);

        return Genus::create($validated);
    }

    public function update(Request $request, $id)
    {
        $genus = Genus::findOrFail($id);
        $genus->update($request->validate([
            'name_ukr' => 'required|string',
            'name_lat' => 'required|string',
        ]));

        return $genus;
    }

    public function destroy($id)
    {
        $genus = Genus::findOrFail($id);
        $genus->delete();

        return response()->noContent();
    }
}
