<?php

namespace App\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Goleadores de una categoría, de mayor a menor. Solo nombre, club y goles.
 */
class TopScorers
{
    public static function forCategory(int $categoryId, int $limit = 20): Collection
    {
        return DB::table('players AS p')
            ->select('p.id', 'p.first_name', 'p.last_name', 'c.name AS club', 'c.color AS club_color')
            ->selectRaw('SUM(gi.goals) AS goals')
            ->join('clubs AS c', 'c.id', 'p.club_id')
            ->join('game_items AS gi', 'gi.player_id', 'p.id')
            ->where('c.category_id', $categoryId)
            ->groupBy('p.id', 'p.first_name', 'p.last_name', 'c.name', 'c.color')
            ->havingRaw('SUM(gi.goals) > 0')
            ->orderByDesc('goals')
            ->orderBy('p.last_name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [...(array) $row, 'goals' => (int) $row->goals]);
    }
}
