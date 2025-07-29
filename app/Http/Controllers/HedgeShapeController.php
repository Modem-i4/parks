<?php

namespace App\Http\Controllers;

use App\Models\HedgeShape;
use Illuminate\Http\Request;

class HedgeShapeController extends Controller
{
    public function index() {
        return HedgeShape::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        return HedgeShape::create($validated);
    }

    public function update(Request $request, $id)
    {
        $hedgeShape = HedgeShape::findOrFail($id);
        $hedgeShape->update($request->validate([
            'name' => 'required',
        ]));

        return $hedgeShape;
    }

    public function destroy($id)
    {
        $hedgeShape = HedgeShape::findOrFail($id);
        $hedgeShape->delete();

        return response()->noContent();
    }
}
