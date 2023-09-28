<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameItemController;
use App\Http\Controllers\GenerateGamesController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\PlayerController;
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
    Route::get('categories/{category}/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::put('groups/{group}/update', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('groups/{group}/destroy', [GroupController::class, 'destroy'])->name('groups.destroy');

    Route::get('club/{club}/jugadores', [PlayerController::class, 'index'])->name('players.index');
    Route::resource('players', PlayerController::class)->only(['store', 'update', 'destroy']);

    Route::get('categoria/{category}/generarpartidos', [GenerateGamesController::class, 'index'])->name('games.vgenerate');
    Route::post('categories/{category}/sgenerate', [GenerateGamesController::class, 'generate'])->name('games.sgenerate');

    // Seleccionar los jugadores para jugar
    Route::get('juego/{game_id}/seleccionar-jugadores', [PlayController::class, 'index'])->name('games.select-players');
    Route::post('play/store/{game}', [PlayController::class, 'store'])->name('play.store');
    Route::get('partido/{game_id}', [GameItemController::class, 'index'])->name('playing.index');
});
