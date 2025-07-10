<?php

namespace App\Http\Controllers;

use App\Models\InfrastructureType;
use Illuminate\Http\Request;

class InfrastructureTypeController extends Controller
{
    public function get() {
        return InfrastructureType::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        return InfrastructureType::create($validated);
    }

    public function update(Request $request, $id)
    {
        $infrastructureType = InfrastructureType::findOrFail($id);
        $infrastructureType->update($request->validate([
            'name' => 'required',
        ]));

        return $infrastructureType;
    }

    public function destroy($id)
    {
        $infrastructureType = InfrastructureType::findOrFail($id);
        $infrastructureType->delete();

        return response()->noContent();
    }
}
