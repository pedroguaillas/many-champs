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
        'paid_santion', 'card_black', 'paid_black', 'note'
    ];
}
