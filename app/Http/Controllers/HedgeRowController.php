<?php

namespace App\Http\Controllers;

use App\Models\HedgeRow;
use Illuminate\Http\Request;

class HedgeRowController extends Controller
{
    public function index() {
        return HedgeRow::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        return HedgeRow::create($validated);
    }

    public function update(Request $request, $id)
    {
        $hedgeRow = HedgeRow::findOrFail($id);
        $hedgeRow->update($request->validate([
            'name' => 'required',
        ]));

        return $hedgeRow;
    }

    public function destroy($id)
    {
        $hedgeRow = HedgeRow::findOrFail($id);
        $hedgeRow->delete();

        return response()->noContent();
    }
}
