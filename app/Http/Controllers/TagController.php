<?php

namespace App\Http\Controllers;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index($type = null)
    {
        if (!$type) {
            return Tag::orderByRaw("FIELD(type, ?) DESC", [TagType::ALL])->get();
        }

        return Tag::whereIn('type', [$type, TagType::ALL])
            ->orderByRaw("FIELD(type, ?) DESC", [TagType::ALL])
            ->get();
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => Rule::in(array_column(TagType::cases(), 'value')),
            'public' => 'boolean',
        ]);

        return Tag::create($validated);
    }

    public function update(Request $request, $id)
    {
        $Tag = Tag::findOrFail($id);
        $Tag->update($request->validate([
            'name' => 'required|string',
            'type' => Rule::in(array_column(TagType::cases(), 'value')),
            'public' => 'boolean',
        ]));

        return $Tag;
    }

    public function destroy($id)
    {
        $Tag = Tag::findOrFail($id);
        $Tag->delete();

        return response()->noContent();
    }
}
