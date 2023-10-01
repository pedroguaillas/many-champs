<?php

namespace App\Http\Controllers;

use App\Models\GameItem;
use App\Models\Player;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GameItemController extends Controller
{
    public function index($game_id)
    {
        $game = json_decode(json_encode(DB::table('games AS g')
            ->select('g.*', 'c1.name AS c1_name', 'c2.name AS c2_name')
            ->join('clubs AS c1', 'club1_id', 'c1.id')
            ->join('clubs AS c2', 'club2_id', 'c2.id')
            ->where('g.id', $game_id)
            ->get()[0]), true);

        $c1_players = Player::select('players.*', 'gi.id AS gi_id', 'gi.goals', 'gi.santion', 'gi.paid_santion', 'gi.change_player_id', 'gi.card_black')
            ->join('game_items AS gi', 'players.id', 'player_id')
            ->where([
                'game_id' => $game['id'],
                'club_id' => $game['club1_id']
            ])->get();

        $c2_players = Player::select('players.*', 'gi.id AS gi_id', 'gi.goals', 'gi.santion', 'gi.paid_santion', 'gi.change_player_id', 'gi.card_black')
            ->join('game_items AS gi', 'players.id', 'player_id')
            ->where([
                'game_id' => $game['id'],
                'club_id' => $game['club2_id']
            ])->get();

        return Inertia::render('Play/Playing', compact('c1_players', 'c2_players', 'game'));
    }

    public function sumGol(GameItem $gameItem)
    {
        $gameItem->goals++;
        $gameItem->save();
    }
}
