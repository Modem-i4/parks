<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    function index() {
        $latestNews = News::with('cover')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();
        return Inertia::render('Home', [
            'news' => $latestNews,
        ]);
    }
}
