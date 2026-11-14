<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Queries\GameSummaries;
use App\Queries\PublicChampionships;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Página pública de un campeonato: categorías, en juego, próximos y resultados.
 */
class ChampionshipController extends Controller
{
    public function __invoke(Team $championship): Response
    {
        return Inertia::render('Public/Championship', [
            'championship' => $championship->only('name', 'slug'),
            'categories' => PublicChampionships::categories($championship->id),
            'live' => GameSummaries::live($championship->id)->limit(10)->get(),
            'upcoming' => GameSummaries::upcoming($championship->id)->limit(6)->get(),
            'results' => GameSummaries::finished($championship->id)->limit(6)->get(),
            'og' => [
                'title' => $championship->name,
                'description' => 'Partidos, resultados, posiciones y goleadores de ' . $championship->name . '.',
            ],
        ]);
    }
}
