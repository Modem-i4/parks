<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
                ->get(['id', 'title', 'body', 'created_at']),
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
                ->get(['id', 'title', 'created_at']),
        ]);
    }
}
