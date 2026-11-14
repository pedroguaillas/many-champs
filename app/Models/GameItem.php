<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'game_id', 'entered_in',
        'change_player_id', 'goals', 'santion',
        'paid_santion', 'card_black', 'paid_black', 'note',
        'pos_x', 'pos_y'
    ];

    protected $casts = [
        'card_black' => 'boolean',
        'paid_santion' => 'date:Y-m-d',
        'paid_black' => 'date:Y-m-d',
        'pos_x' => 'float',
        'pos_y' => 'float',
    ];

    /**
     * Última posición conocida de cada jugador en partidos anteriores.
     * Devuelve [player_id => ['pos_x' => float, 'pos_y' => float]].
     */
    public static function lastPositions(array $playerIds, int $exceptGameId): array
    {
        if (empty($playerIds)) {
            return [];
        }

        return static::whereIn('player_id', $playerIds)
            ->where('game_id', '!=', $exceptGameId)
            ->whereNotNull('pos_x')
            ->whereNotNull('pos_y')
            ->orderByDesc('id')
            ->get(['player_id', 'pos_x', 'pos_y'])
            ->unique('player_id')
            ->mapWithKeys(fn ($item) => [$item->player_id => ['pos_x' => $item->pos_x, 'pos_y' => $item->pos_y]])
            ->all();
    }
}
