<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Team;
use App\Queries\GameSummaries;
use App\Queries\Standings;
use App\Queries\TopScorers;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Categoría pública: partidos, tabla de posiciones y goleadores.
 */
class CategoryController extends Controller
{
    public function __invoke(Team $championship, int $category): Response
    {
        // La categoría tiene que ser de este campeonato
        $category = Category::where('team_id', $championship->id)->findOrFail($category);

        return Inertia::render('Public/Category', [
            'championship' => $championship->only('name', 'slug'),
            'category' => $category->only('id', 'name', 'gender'),
            'games' => GameSummaries::forChampionship($championship->id)
                ->where('cat.id', $category->id)
                ->orderBy('games.date')->orderBy('games.time')
                ->get(),
            'standings' => Standings::forCategory($category),
            'scorers' => TopScorers::forCategory($category->id),
            'og' => [
                'title' => $category->name . ' · ' . $championship->name,
                'description' => 'Partidos, posiciones y goleadores de ' . $category->name . '.',
            ],
        ]);
    }
}
