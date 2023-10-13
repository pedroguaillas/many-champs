<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlayController extends Controller
{
    public function index($game_id)
    {
        $game = json_decode(json_encode(DB::table('games AS g')
            ->select(
                'g.*',
                'c1.name AS c1_name',
                'c2.name AS c2_name',
                DB::raw('(SELECT SUM(amount) FROM payments AS p WHERE club_id = club1_id) AS paid1,(SELECT SUM(amount) FROM payments AS p WHERE club_id = club2_id) AS paid2')
            )
            ->join('clubs AS c1', 'club1_id', 'c1.id')
            ->join('clubs AS c2', 'club2_id', 'c2.id')
            ->where('g.id', $game_id)
            ->get()[0]), true);

        $q = '(SELECT santion FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS santion,';
        $qId = '(SELECT id FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS gi_saction_id,';
        $qblack = '(SELECT card_black FROM game_items AS gi WHERE player_id = players.id AND card_black = 1 AND paid_black IS NULL LIMIT 1) AS black, ';
        $qblackId = '(SELECT id FROM game_items AS gi WHERE player_id = players.id AND card_black = 1 AND paid_black IS NULL LIMIT 1) AS gi_back_id';

        $club1_players = Player::select(DB::raw('id,first_name,last_name,' . $q . $qId . $qblack . $qblackId))
            ->where('club_id', $game['club1_id'])->get();

        $club2_players = Player::select(DB::raw('id,first_name,last_name,' . $q . $qId . $qblack . $qblackId))
            ->where('club_id', $game['club2_id'])->get();

        return Inertia::render('Play/SelectPlayersToPlay', compact('club1_players', 'club2_players', 'game'));
    }

    public function store(Request $request, Game $game)
    {
        $game_items = [];

        // Formar un nuevo array para insertar en game_items

        foreach ($request->clubs1 as $key => $value) {
            array_push($game_items, [
                'player_id' => $value,
                'entered_in' => 'inicio'
            ]);
        }

        foreach ($request->clubs2 as $key => $value) {
            array_push($game_items, [
                'player_id' => $value,
                'entered_in' => 'inicio'
            ]);
        }

        $game->gameitems()->createMany($game_items);
        $game->state = 'jugando';
        $game->save();

        return to_route('playing.index', $game->id);
    }
}
