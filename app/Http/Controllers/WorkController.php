<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'green_id' => 'required|integer|exists:green,id',
            'recommendation_id' => 'required|integer|exists:recommendations,id',
            'notes' => 'sometimes|nullable|string',
        ]);
        $validated['recommendation_date'] = now()->toDateString();
        $validated['recommender_id'] = Auth::id();
        return Work::create($validated)->load(['recommendation', 'recommender', 'executor']);
    }

    public function update(Request $request, $id)
    {
        $works = Work::findOrFail($id);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'recommendation_id' => 'sometimes|integer|exists:recommendations,id',
        ]);
        $works->update($validated);
        return $works->load(['recommendation', 'recommender', 'executor']);
    }

    public function complete(Request $request, $id)
    {
        $works = Work::findOrFail($id);
        $works->update([
            'execution_date' => now(),
            'executor_id' => Auth::id()
        ]);
        return $works->load(['recommendation', 'recommender', 'executor']);
    }

    public function revert(Request $request, $id)
    {
        $works = Work::findOrFail($id);
        $works->update([
            'execution_date' => null,
            'executor_id' => null
        ]);
        return $works->load(['recommendation', 'recommender', 'executor']);
    }

    public function destroy($id)
    {
        $works = Work::findOrFail($id);
        $works->delete();
        return response()->noContent();
    }
}
