<?php

namespace App\Http\Controllers;

use App\Models\Subplot;
use Illuminate\Http\Request;

class SubplotController extends Controller
{
    public function index(Request $request) {
        $plotId = $request->plotId;
        if($plotId) {
            return Subplot::where('plot_id', $plotId)->get();
        }
        return Subplot::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'plot_id' => ['required', 'integer', 'exists:plots,id'],
        ]);

        return Subplot::create($validated);
    }

    public function update(Request $request, $id)
    {
        $subplot = Subplot::findOrFail($id);
        $subplot->update($request->validate([
            'name' => 'required',
        ]));

        return $subplot;
    }

    public function destroy($id)
    {
        $plot = Subplot::findOrFail($id);
        $plot->delete();

        return response()->noContent();
    }
}
