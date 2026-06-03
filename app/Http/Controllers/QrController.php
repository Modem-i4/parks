<?php

namespace App\Http\Controllers;

class QrController extends Controller
{
    private const ENDPOINTS = [
        'plan' => '/files/strategic-plan.pdf'
    ];

    public function index($slug)
    {
        $target = self::ENDPOINTS[$slug] ?? '/';
        return redirect($target);
    }
}
