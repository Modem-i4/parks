<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Services\StatsService;
use App\Models\Park;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(StatsService $stats)
    {
        $latestNews = News::with('cover')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->take(3)
            ->get(['id','title','published_at']);
        $parks = Park::with('icon')->get();
        return Inertia::render('Home', [
            'news'  => $latestNews,
            'stats' => $stats->get(),
            'parks' => $parks
        ]);
    }
}
