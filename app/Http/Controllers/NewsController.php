<?php
namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index()
    {
        return Inertia::render('News/Index', [
            'news' => News::with('cover')
                ->latest()
                ->get(['id', 'title', 'body', 'published_at']),
        ]);
    }
    public function single($id)
    {
        return Inertia::render('News/Single', [
            'news' => News::with('cover', 'author')->findOrFail($id),
            'lastNews' => News::with('cover')
                ->latest()
                ->where('id', '!=', $id)
                ->take(5)
                ->get(['id', 'title', 'published_at']),
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255'
        ]);

        return News::create($validated);
    }
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $news->update($request->validate([
            'title' => 'sometimes|required|string|min:3|max:255',
            'body' => 'sometimes|string',
            'published_at' => 'sometimes|nullable|date'
        ]));

        return $news->load('cover');
    }
}
