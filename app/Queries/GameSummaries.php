<?php

namespace App\Queries;

use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;

/**
 * Partidos de un campeonato en formato de lista: clubes, colores, categoría,
 * grupo, fase y marcador. Solo datos que se pueden mostrar en público.
 */
class GameSummaries
{
    public static function forChampionship(int $teamId): Builder
    {
        $goals = fn (string $club) => "(SELECT COALESCE(SUM(gi.goals), 0) FROM game_items AS gi "
            . "JOIN players AS p ON p.id = gi.player_id "
            . "WHERE gi.game_id = games.id AND p.club_id = games.$club)";

        return Game::query()
            ->select(
                'games.id', 'games.state', 'games.date', 'games.time',
                'c1.name AS c1name', 'c2.name AS c2name',
                'c1.color AS c1color', 'c2.color AS c2color',
                'cat.id AS category_id', 'cat.name AS category',
                'grp.name AS group_name', 'pr.description AS phase',
            )
            ->selectRaw($goals('club1_id') . ' AS score1')
            ->selectRaw($goals('club2_id') . ' AS score2')
            ->join('clubs AS c1', 'games.club1_id', 'c1.id')
            ->join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->join('categories AS cat', 'cat.id', 'c1.category_id')
            ->leftJoin('groups AS grp', 'grp.id', 'c1.group_id')
            ->leftJoin('progress AS pr', 'pr.id', 'games.progress_id')
            ->where('cat.team_id', $teamId);
    }

    public static function live(int $teamId): Builder
    {
        return self::forChampionship($teamId)
            ->where('games.state', 'jugando')
            ->orderBy('games.date')->orderBy('games.time');
    }

    public static function upcoming(int $teamId): Builder
    {
        return self::forChampionship($teamId)
            ->whereIn('games.state', ['creado', 'planificado'])
            ->whereDate('games.date', '>=', now()->toDateString())
            ->orderBy('games.date')->orderBy('games.time');
    }

    public static function finished(int $teamId): Builder
    {
        return self::forChampionship($teamId)
            ->where('games.state', 'finalizado')
            ->orderByDesc('games.date')->orderByDesc('games.time');
    }
}
