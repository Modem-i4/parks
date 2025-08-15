<?php
namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 6;
        $p = $this->baseQuery($request)
            ->paginate($perPage)
            ->withQueryString();
        return Inertia::render('News/Index', [
            'news'      => $p->items(),
            'nextPage' => $this->nextPage($p),
            'query'     => (string) $request->query('q',''),
        ]);
    }

    public function search(Request $request)
    {
        $perPage = (int) $request->query('per_page', 6);
        $p = $this->baseQuery($request)->paginate($perPage);
        return response()->json([
            'data'      => $p->items(),
            'nextPage' => $this->nextPage($p),
        ]);
    }

    protected function baseQuery(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));

        return News::with('cover')
            ->select(['id','title','body','published_at'])
            ->when($q !== '', function (Builder $b) use ($q) {
                $b->where(function (Builder $w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('body',  'like', "%{$q}%");
                });
            })
            ->orderByDesc('published_at')->orderByDesc('id');
    }

    private function nextPage(LengthAwarePaginator $p): ?int
    {
        return $p->hasMorePages() ? $p->currentPage() + 1 : null;
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
