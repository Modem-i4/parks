<?php
namespace App\Http\Controllers\Species;

use App\Enums\GreenType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class FamilyController extends Controller
{
    public function index($type)
    {
        return Family::where('type', $type)->get();
    }

    public function getWithStructure(String $type) {
        return Family::with(['genus', 'genus.species'])->where('type', $type)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ukr' => 'required|string',
            'name_lat' => 'nullable|string',
            'type' => ['required', new Enum(GreenType::class)],
        ]);

        return Family::create($validated);
    }

    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);
        $family->update($request->validate([
            'name_ukr' => 'required|string',
            'name_lat' => 'nullable|string',
        ]));

        return $family;
    }

    public function destroy($id)
    {
        $family = Family::findOrFail($id);
        $family->delete();

        return response()->noContent();
    }
}
