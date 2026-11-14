<?php

namespace App\Queries;

use App\Models\Player;
use Illuminate\Support\Collection;

/**
 * Planilla pública de un partido: quién juega, goles, tarjetas, cambios y
 * posición en cancha. Lista blanca de columnas: nunca salen cédula, teléfono,
 * fecha de nacimiento, foto, pagos ni tarjetas negras.
 */
class MatchSheet
{
    public static function players(int $gameId, int $clubId): Collection
    {
        return Player::query()
            ->select(
                'players.id', 'players.first_name', 'players.last_name', 'players.t_shirt',
                'gi.id AS gi_id', 'gi.goals', 'gi.santion', 'gi.change_player_id', 'gi.pos_x', 'gi.pos_y',
            )
            ->join('game_items AS gi', 'players.id', 'gi.player_id')
            ->where('gi.game_id', $gameId)
            ->where('players.club_id', $clubId)
            ->orderBy('gi.id')
            ->get()
            ->map(fn (Player $player) => [
                'id' => $player->id,
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                't_shirt' => $player->t_shirt,
                'gi_id' => $player->gi_id,
                'goals' => (int) $player->goals,
                'santion' => $player->santion,
                'change_player_id' => $player->change_player_id,
                'pos_x' => $player->pos_x === null ? null : (float) $player->pos_x,
                'pos_y' => $player->pos_y === null ? null : (float) $player->pos_y,
            ]);
    }
}
