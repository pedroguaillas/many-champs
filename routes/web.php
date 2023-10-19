<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameItemController;
use App\Http\Controllers\GenerateGamesController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PositionTableController;
use App\Http\Controllers\SantionController;
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

    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::get('seleccionar-categoria/{type}', [HomeController::class, 'sendRedirect'])->name('select.category');

    // Clubs
    Route::get('categoria/{category}/clubs', [ClubController::class, 'index'])->name('clubs.index');
    Route::resource('clubs', ClubController::class)->only(['store', 'update', 'destroy']);

    Route::get('categoria/{category}/juegos', [GameController::class, 'index'])->name('games.index');
    Route::get('categories/{category}/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('categories/{category}/games/store', [GameController::class, 'store'])->name('games.store');
    Route::get('games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::put('games/{game}/update', [GameController::class, 'update'])->name('games.update');
    Route::delete('games/{game}/destroy', [GameController::class, 'destroy'])->name('games.destroy');

    // Finalizar el partido
    Route::put('games/{game}/ended', [GameController::class, 'ended'])->name('games.ended');

    Route::get('club/{club}/jugadores', [PlayerController::class, 'index'])->name('players.index');
    Route::resource('players', PlayerController::class)->only(['store', 'update', 'destroy']);

    Route::get('categoria/{category}/generarpartidos', [GenerateGamesController::class, 'index'])->name('games.vgenerate');
    Route::post('categories/{category}/sgenerate', [GenerateGamesController::class, 'generate'])->name('games.sgenerate');

    // Seleccionar los jugadores para jugar
    Route::get('juego/{game_id}/seleccionar-jugadores', [PlayController::class, 'index'])->name('games.select-players');
    Route::post('play/store/{game}', [PlayController::class, 'store'])->name('play.store');
    Route::get('partido/{game_id}', [GameItemController::class, 'index'])->name('playing.index');

    // Durante el partido
    Route::post('gameitems/store', [GameItemController::class, 'store'])->name('gameitems.store');
    Route::put('gameitems/{gameitem_id}/update', [GameItemController::class, 'update'])->name('gameitems.update');

    // Patch
    Route::patch('gameitems/{gameitem_id}/patch', [GameItemController::class, 'patch'])->name('gameitems.patch');

    // Traer losjugadores para el cambio o completar
    Route::post('gameitems/{game}/players', [GameItemController::class, 'getPlayers'])->name('gameitems.players');

    Route::get('club/{club}/pagos', [PaymentController::class, 'index'])->name('payments.index');
    Route::resource('payments', PaymentController::class)->only(['store', 'update', 'destroy']);

    // Calendario
    Route::get('calendario/{date?}', [HomeController::class, 'calendar'])->name('calendar');

    // Tabla de posiciones
    Route::get('categoria/{category}/tabla-de-posiciones', [PositionTableController::class, 'index'])->name('tabla-de-posiciones.index');

    // Sanciones
    Route::get('sanciones/{search?}', [SantionController::class, 'index'])->name('santions.index');
    // Dario
    Route::get('diario', [HomeController::class, 'diary'])->name('diary');

    // Categorias
    Route::get('categorias', [CategoryController::class, 'index'])->name('categories.index');
    Route::resource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);

    // Grupos
    Route::get('categoria/{category}/grupos', [GroupController::class, 'index'])->name('groups.index');
    Route::resource('groups', GroupController::class)->only(['store', 'update', 'destroy']);
});
