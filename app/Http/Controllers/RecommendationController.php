<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index()
    {
        return Recommendation::get();
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string'
        ]);

        return Recommendation::create($validated);
    }

    public function update(Request $request, $id)
    {
        $recommendation = Recommendation::findOrFail($id);
        $recommendation->update($request->validate([
            'name' => 'required|string'
        ]));

        return $recommendation;
    }

    public function destroy($id)
    {
        $recommendation = Recommendation::findOrFail($id);
        $recommendation->delete();

        return response()->noContent();
    }
}
