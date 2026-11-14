<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Team;
use App\Queries\GameSummaries;
use App\Queries\MatchSheet;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Partido público: marcador y cancha de solo lectura.
 */
class GameController extends Controller
{
    public function __invoke(Team $championship, int $game): Response
    {
        // El partido tiene que ser de este campeonato
        $summary = GameSummaries::forChampionship($championship->id)
            ->addSelect('games.club1_id', 'games.club2_id')
            ->where('games.id', $game)
            ->firstOrFail();

        return Inertia::render('Public/Game', [
            'championship' => $championship->only('name', 'slug'),
            'game' => $summary,
            'home' => MatchSheet::players($summary->id, $summary->club1_id),
            'away' => MatchSheet::players($summary->id, $summary->club2_id),
            'og' => [
                'title' => $this->headline($summary) . ' · ' . $championship->name,
                'description' => $summary->category . ($summary->state === 'jugando' ? ' · En vivo' : ''),
            ],
        ]);
    }

    /** "Leones 2 - 1 Tigres" si ya empezó; "Leones vs Tigres" si no */
    private function headline(Game $game): string
    {
        return in_array($game->state, ['jugando', 'finalizado'])
            ? "{$game->c1name} {$game->score1} - {$game->score2} {$game->c2name}"
            : "{$game->c1name} vs {$game->c2name}";
    }
}
