<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::get('seleccionar_categoria/{type}', [HomeController::class, 'sendRedirect'])->name('seleccionar.categoria');
    Route::get('categories/{category}/clubs', [ClubController::class, 'index'])->name('clubs.index');
    Route::resource('clubs', ClubController::class)->only(['store', 'update', 'destroy']);
    Route::get('categories/{category}/games', [GameController::class, 'index'])->name('games.index');
    Route::get('categories/{category}/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('categories/{category}/games/store', [GameController::class, 'store'])->name('games.store');
    Route::get('games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::put('games/{game}/update', [GameController::class, 'update'])->name('games.update');
    Route::delete('games/{game}/destroy', [GameController::class, 'destroy'])->name('games.destroy');
    Route::get('categories/{category}/generate_games', [GameController::class, 'generateGames'])->name('games.generate_games');
});
