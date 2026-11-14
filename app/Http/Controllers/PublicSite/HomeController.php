<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Queries\PublicChampionships;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Portada: presentación y campeonatos compartidos.
 */
class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'championships' => PublicChampionships::list(),
        ]);
    }
}
