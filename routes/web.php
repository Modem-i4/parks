<?php

use App\Http\Controllers\FilterConfigController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Main nav
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');
Route::get('/home', function () {
    return Inertia::render('Home');
});

Route::get('/parks/{id?}', [ParkController::class, 'index'])->name('parks');

Route::get('/news', function () {
    return Inertia::render('News');
})->name('news');

// END main nav

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->group(function () {
        // General admin page
        Route::get('/', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        // Admin versions of default pages
        Route::get('/parks', function () {
            return Inertia::render('Parks');
        })->name('admin.parks');

        // Role specific pages
        
        Route::get('/news', function () {
            return Inertia::render('Admin/News');
        })->name('admin.news');

        Route::middleware('auth', 'role:admin')->group(function () {
            Route::get('users', function () {
                return Inertia::render('Admin/Users');
            })->name('admin.users');
            Route::get('dictionaries', function () {
                return Inertia::render('Admin/Dictionaries');
            })->name('admin.dictionaries');
        });
    });
});

Route::prefix('api')->group(function () {

    Route::get('/parks/{id}/media', [ParkController::class, 'media'])->name('parks.media');
    Route::get('/parks', [ParkController::class, 'getParksList'])->name('parks.list');
    Route::post('/parks/{id}/markers', [MarkerController::class, 'filterParkMarkers'])->name('parks.markers');

    Route::get('/markers/filters-config', [MarkerController::class, 'getFilters'])->name('filters');
    Route::get('/markers/{id}', [MarkerController::class, 'getSingleMarker'])->name('marker');
    Route::get('/markers/{id}/media', [MarkerController::class, 'media'])->name('marker.media');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
