<?php

namespace App\Http\Controllers;

use App\Models\GameItem;
use App\Models\Player;
use App\Models\Game;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        GameItem::create($request->all());
    }

    public function update(Request $request, int $gameitem_id)
    {
        $gameItem = GameItem::findOrFail($gameitem_id);
        // Registrar goles y targetas
        $gameItem->update($request->all());
    }

    // Para pagos
    public function patch(Request $request, int $gameitem_id)
    {
        $gameItem = GameItem::findOrFail($gameitem_id);

        $carbon = Carbon::now();
        if ($request->patch === 'santion') {
            $gameItem->update(['paid_santion' => $carbon->toDateString()]);
        } else {
            $gameItem->update(['paid_black' => $carbon->toDateString()]);
        }
    }

    public function getPlayers(Request $request, Game $game)
    {
        // La consulta debe:
        // EL juador no debe estar jugando
        $players = Player::select(
            'players.id',
            'first_name',
            'last_name',
            DB::raw('(SELECT santion FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS santion'),
            DB::raw('(SELECT card_black FROM game_items AS gi WHERE player_id = players.id AND card_black = 1 AND paid_black IS NULL LIMIT 1) AS black')
        )
            ->whereNotIn(
                'id',
                function ($query) use ($game) {
                    $query->select('player_id')
                        ->from(with(new GameItem)->getTable())
                        ->where('game_id', $game->id);
                }
            )
            // A demas el jugar debe ser del club1_id o club2_id
            ->where('club_id', $game->{'club' . $request->type . '_id'})
            ->get();

        return response()->json(['result' => $players]);
    }
}
