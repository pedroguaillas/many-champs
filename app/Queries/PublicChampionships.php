<?php

namespace App\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Campeonatos que su organizador compartió, para la portada.
 * Primero los que tienen partidos en juego.
 */
class PublicChampionships
{
    public static function list(int $limit = 24): Collection
    {
        $gamesOf = 'FROM games AS g JOIN clubs AS gc ON gc.id = g.club1_id '
            . 'JOIN categories AS c ON c.id = gc.category_id WHERE c.team_id = teams.id';

        return DB::table('teams')
            ->select('teams.name', 'teams.slug')
            ->selectRaw('(SELECT COUNT(*) FROM categories WHERE categories.team_id = teams.id) AS categories')
            ->selectRaw("(SELECT COUNT(*) $gamesOf AND g.state = 'jugando') AS live")
            ->selectRaw("(SELECT COUNT(*) $gamesOf) AS games")
            ->where('teams.is_public', true)
            ->whereNotNull('teams.slug')
            ->orderByDesc('live')
            ->orderByDesc('games')
            ->orderBy('teams.name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'slug' => $row->slug,
                'categories' => (int) $row->categories,
                'live' => (int) $row->live,
                'games' => (int) $row->games,
            ]);
    }

    /** Categorías de un campeonato con su avance */
    public static function categories(int $teamId): Collection
    {
        $gamesOf = 'FROM games AS g JOIN clubs AS gc ON gc.id = g.club1_id WHERE gc.category_id = c.id';

        return DB::table('categories AS c')
            ->select('c.id', 'c.name', 'c.gender')
            ->selectRaw('(SELECT COUNT(*) FROM clubs WHERE clubs.category_id = c.id) AS clubs')
            ->selectRaw("(SELECT COUNT(*) $gamesOf) AS games")
            ->selectRaw("(SELECT COUNT(*) $gamesOf AND g.state = 'finalizado') AS finished")
            ->selectRaw("(SELECT COUNT(*) $gamesOf AND g.state = 'jugando') AS live")
            ->where('c.team_id', $teamId)
            ->orderBy('c.name')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'gender' => $row->gender,
                'clubs' => (int) $row->clubs,
                'games' => (int) $row->games,
                'finished' => (int) $row->finished,
                'live' => (int) $row->live,
            ]);
    }
}
