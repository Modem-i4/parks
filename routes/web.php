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
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\HedgeRowController;
use App\Http\Controllers\HedgeShapeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Main nav
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::redirect('/home', '/');

Route::get('/parks/{id?}', [ParkController::class, 'index'])->name('parks');
Route::get('/m/{inv?}', [ParkController::class, 'parksMarkerIndex'])->name('parks.marker');

Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{id}', [NewsController::class, 'single'])->name('news.single');

// END main nav

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->group(function () {
        // General admin page
        Route::get('/', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        // Admin versions of default pages
        Route::get('/parks', [ParkController::class, 'index'])->name('admin.parks');

        // Role specific pages
        Route::middleware('auth', 'role:editor')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('admin.users');
            Route::get('dictionaries', function () {
                return Inertia::render('Admin/Dictionaries');
            })->name('admin.dictionaries');
        });
    });
});
Route::prefix('api')->group(function () {
    // ========= general =========
    // Parks
    Route::get('/parks/{id}/media', [ParkController::class, 'media'])->name('parks.media');
    Route::get('/parks', [ParkController::class, 'getParksList'])->name('parks.list');
    Route::post('/parks/{id}/markers', [MarkerController::class, 'filterParkMarkers'])->name('parks.markers');
    // Markers
    Route::get('/markers/filters-config', [MarkerController::class, 'getFilters'])->name('filters');
    Route::get('/markers/inv/{inv}', [MarkerController::class, 'getSingleMarkerByInv'])->name('marker.byInv');
    Route::get('/markers/{id}', [MarkerController::class, 'getSingleMarker'])->name('marker');
    Route::get('/markers/{id}/media', [MarkerController::class, 'media'])->name('marker.media');
    // Media
    Route::get('/media', [MediaController::class, 'index']);
    // News
    Route::get('/news', [NewsController::class, 'search']);
    // Taxonomy (readonly)
    Route::get('/families/{type}', [FamilyController::class, 'index']);
    Route::get('/families-full-structure/{type}', [FamilyController::class, 'getWithStructure']);
    Route::get('/genus/{type}', [GenusController::class, 'index']);
    Route::get('/species/{type}', [SpeciesController::class, 'index']);
    Route::get('/infrastructureType', [InfrastructureTypeController::class, 'get']);
    Route::get('/tags/{type?}', [TagController::class, 'index']);
    // ========= view =========
    Route::middleware('can:view')->group(function () {
        // Media Lib
        Route::get('/media-library', [MediaLibraryController::class, 'index']);
        // Taxonomy (readonly)
        Route::get('/recommendations', [RecommendationController::class, 'index']);
        Route::get('/hedgeRows', [HedgeRowController::class, 'index']);
        Route::get('/hedgeShapes', [HedgeShapeController::class, 'index']);
        Route::get('/plots', [PlotController::class, 'index']);
    });
    // ========= export =========
    Route::middleware('can:export')->group(function () {
        Route::post('/markers/export', [MarkerController::class, 'export'])->name('marker.export');
    });
    // ========= edit =========
    Route::middleware('can:edit')->group(function () {
        Route::patch('/parks/{id}', [ParkController::class, 'update'])->name('parks.update');
        Route::patch('/markers/{id}', [MarkerController::class, 'update'])->name('marker.update');

        Route::delete('/media-library/{mediaLibrary}', [MediaLibraryController::class, 'destroy']);
        Route::patch('/plots/{id}', [PlotController::class, 'update']);
        Route::delete('/plots/{id}', [PlotController::class, 'destroy']);
    });
    // ========= addMarkers =========
    Route::middleware('can:addMarkers')->group(function () {
        Route::post('/markers', [MarkerController::class, 'store'])->name('marker.create');
    });
    // ========= deleteMarkers =========
    Route::middleware('can:deleteMarkers')->group(function () {
        Route::delete('/markers/{id}', [MarkerController::class, 'destroy']);
    });
    // ========= upload =========
    Route::middleware('can:upload')->group(function () {
        Route::post('/media-library', [MediaLibraryController::class, 'store']);
        Route::post('/media/sync', [MediaController::class, 'sync']);
    });
    // ========= import =========
    Route::middleware('can:import')->group(function () {
        Route::post('/markers/import', [MarkerController::class, 'import'])->name('marker.import');
        Route::post('/markers/import/preview', [MarkerController::class, 'preview'])->name('marker.import.preview');
    });
    // ========= editDictionaries =========
    Route::middleware('can:editDictionaries')->group(function () {
        // Families
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
        Route::post('/infrastructureType', [InfrastructureTypeController::class, 'store']);
        Route::patch('/infrastructureType/{id}', [InfrastructureTypeController::class, 'update']);
        Route::delete('/infrastructureType/{id}', [InfrastructureTypeController::class, 'destroy']);
        // Tags
        Route::post('/tags', [TagController::class, 'store']);
        Route::patch('/tags/{id}', [TagController::class, 'update']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);
        // Recommendations
        Route::post('/recommendations', [RecommendationController::class, 'store']);
        Route::patch('/recommendations/{id}', [RecommendationController::class, 'update']);
        Route::delete('/recommendations/{id}', [RecommendationController::class, 'destroy']);
        // Hedge Rows
        Route::post('/hedgeRows', [HedgeRowController::class, 'store']);
        Route::patch('/hedgeRows/{id}', [HedgeRowController::class, 'update']);
        Route::delete('/hedgeRows/{id}', [HedgeRowController::class, 'destroy']);
        // Hedge Shapes
        Route::post('/hedgeShapes', [HedgeShapeController::class, 'store']);
        Route::patch('/hedgeShapes/{id}', [HedgeShapeController::class, 'update']);
        Route::delete('/hedgeShapes/{id}', [HedgeShapeController::class, 'destroy']);
        // Plots
        Route::post('/plots', [PlotController::class, 'store']);
    });
    // ========= assignWork =========
    Route::middleware('can:assignWork')->group(function () {
        Route::post('/works', [WorkController::class, 'store']);
        Route::post('/works/bulk', [WorkController::class, 'bulkStore']);
        Route::patch('/works/{id}', [WorkController::class, 'update']);
        Route::delete('/works/{id}', [WorkController::class, 'destroy']);
    });
    // ========= completeWork =========
    Route::middleware('can:completeWork')->group(function () {
        Route::patch('/works/{id}/complete', [WorkController::class, 'complete']);
        Route::patch('/works/{id}/revert', [WorkController::class, 'revert']);
    });
    // ========= editNews =========
    Route::middleware('can:editNews')->group(function () {
        Route::post('/news', [NewsController::class, 'store']);
        Route::patch('/news/{id}', [NewsController::class, 'update']);
        Route::delete('/news/{id}', [NewsController::class, 'destroy']);
    });
    // ========= adminUsers =========
    Route::middleware('can:adminUsers')->group(function () {
        Route::patch('users/{id}/role', [UserController::class, 'updateRole'])->name('users.role');
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
