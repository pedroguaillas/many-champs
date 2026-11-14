<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlayController extends Controller
{
    public function index(int $game_id)
    {
        // SEGURIDAD: Validar que game_id es un entero y existe
        $game = DB::table('games AS g')
            ->select(
                'g.*',
                'c1.name AS c1_name', 'c1.category_id',
                'c2.name AS c2_name',
                'pr.description AS phase',
                DB::raw('(SELECT SUM(amount) FROM payments AS p WHERE club_id = club1_id) AS paid1,(SELECT SUM(amount) FROM payments AS p WHERE club_id = club2_id) AS paid2')
            )
            ->join('clubs AS c1', 'club1_id', 'c1.id')
            ->join('clubs AS c2', 'club2_id', 'c2.id')
            ->leftJoin('progress AS pr', 'g.progress_id', 'pr.id')
            ->where('g.id', $game_id)
            ->first();

        abort_if(!$game, 404);

        // La alineación inicial solo se registra una vez
        if (in_array($game->state, ['jugando', 'finalizado'])) {
            return to_route('playing.index', $game_id);
        }

        $game = json_decode(json_encode($game), true);

        $q = '(SELECT santion FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS santion,';
        $qId = '(SELECT id FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS gi_saction_id,';
        $qblack = '(SELECT card_black FROM game_items AS gi WHERE player_id = players.id AND card_black = true AND paid_black IS NULL LIMIT 1) AS black, ';
        $qblackId = '(SELECT id FROM game_items AS gi WHERE player_id = players.id AND card_black = true AND paid_black IS NULL LIMIT 1) AS gi_back_id';

        $club1_players = Player::selectRaw('id,first_name,last_name,photo,t_shirt,' . $q . $qId . $qblack . $qblackId)
            ->where('club_id', $game['club1_id'])->orderBy('first_name')->get();

        $club2_players = Player::selectRaw('id,first_name,last_name,photo,t_shirt,' . $q . $qId . $qblack . $qblackId)
            ->where('club_id', $game['club2_id'])->orderBy('first_name')->get();

        return Inertia::render('Play/SelectPlayersToPlay', compact('club1_players', 'club2_players', 'game'));
    }

    public function store(Request $request, Game $game)
    {
        $request->validate([
            'clubs1' => 'required|array|min:1',
            'clubs1.*' => 'required|integer|exists:players,id',
            'clubs2' => 'required|array|min:1',
            'clubs2.*' => 'required|integer|exists:players,id',
        ]);

        // Evita duplicar titulares si se reenvía el formulario
        if (in_array($game->state, ['jugando', 'finalizado'])) {
            return to_route('playing.index', $game->id);
        }

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
