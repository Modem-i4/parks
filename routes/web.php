<?php

use App\Http\Controllers\Species\FamilyController;
use App\Http\Controllers\Species\GenusController;
use App\Http\Controllers\Species\SpeciesController;
use App\Http\Controllers\InfrastructureTypeController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaLibraryController;
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

// TODO: Don't forget to assign correct roles access
Route::prefix('api')->group(function () {

    // Parks
    Route::get('/parks/{id}/media', [ParkController::class, 'media'])->name('parks.media');
    Route::get('/parks', [ParkController::class, 'getParksList'])->name('parks.list');
    Route::post('/parks/{id}/markers', [MarkerController::class, 'filterParkMarkers'])->name('parks.markers');

    // Markers
    Route::get('/markers/filters-config', [MarkerController::class, 'getFilters'])->name('filters');
    Route::get('/markers/{id}', [MarkerController::class, 'getSingleMarker'])->name('marker');
    Route::get('/markers/{id}/media', [MarkerController::class, 'media'])->name('marker.media');

    //// Taxonomy
    // Families
    Route::get('/families', [FamilyController::class, 'index']);
    Route::get('/families-full-structure/{type}', [FamilyController::class, 'getWithStructure']);
    Route::post('/families', [FamilyController::class, 'store']);
    Route::patch('/families/{id}', [FamilyController::class, 'update']);
    Route::delete('/families/{id}', [FamilyController::class, 'destroy']);

    // Genus
    Route::post('/genus', [GenusController::class, 'store']);
    Route::patch('/genus/{id}', [GenusController::class, 'update']);
    Route::delete('/genus/{id}', [GenusController::class, 'destroy']);

    // Species
    Route::post('/species', [SpeciesController::class, 'store']);
    Route::patch('/species/{id}', [SpeciesController::class, 'update']);
    Route::delete('/species/{id}', [SpeciesController::class, 'destroy']);

    // InfrastructureType
    Route::get('/infrastructureType', [InfrastructureTypeController::class, 'get']);
    Route::post('/infrastructureType', [InfrastructureTypeController::class, 'store']);
    Route::patch('/infrastructureType/{id}', [InfrastructureTypeController::class, 'update']);
    Route::delete('/infrastructureType/{id}', [InfrastructureTypeController::class, 'destroy']);

    //// Media
    Route::prefix('media-library')->group(function () {
        Route::get('/', [MediaLibraryController::class, 'index']);
        Route::post('/', [MediaLibraryController::class, 'store']);
        Route::delete('/{mediaLibrary}', [MediaLibraryController::class, 'destroy']);
    });

    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::post('/sync', [MediaController::class, 'sync']);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
