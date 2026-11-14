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
    public function index(int $game_id)
    {
        // SEGURIDAD: Validar que el game existe antes de acceder al índice [0]
        $gameRecord = DB::table('games AS g')
            ->select('g.*', 'c1.name AS c1_name', 'c2.name AS c2_name', 'c1.color AS c1_color', 'c2.color AS c2_color', 'c1.category_id')
            ->join('clubs AS c1', 'club1_id', 'c1.id')
            ->join('clubs AS c2', 'club2_id', 'c2.id')
            ->where('g.id', $game_id)
            ->first();

        abort_if(!$gameRecord, 404);

        $game = json_decode(json_encode($gameRecord), true);

        $c1_players = Player::select('players.*', 'gi.id AS gi_id', 'goals', 'santion', 'paid_santion', 'change_player_id', 'card_black', 'gi.pos_x', 'gi.pos_y')
            ->join('game_items AS gi', 'players.id', 'player_id')
            ->where([
                'game_id' => $game['id'],
                'club_id' => $game['club1_id']
            ])->orderBy('gi.id')->get();

        $c2_players = Player::select('players.*', 'gi.id AS gi_id', 'goals', 'santion', 'paid_santion', 'change_player_id', 'card_black', 'gi.pos_x', 'gi.pos_y')
            ->join('game_items AS gi', 'players.id', 'player_id')
            ->where([
                'game_id' => $game['id'],
                'club_id' => $game['club2_id']
            ])->orderBy('gi.id')->get();

        return Inertia::render('Play/Playing', compact('c1_players', 'c2_players', 'game'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'game_id' => 'required|integer|exists:games,id',
            'entered_in' => 'required|string|max:50',
            'change_player_id' => 'nullable|integer|exists:players,id',
        ]);

        $outgoing = null;
        if ($request->filled('change_player_id')) {
            $outgoing = GameItem::where([
                'player_id' => $request->change_player_id,
                'game_id' => $request->game_id
            ])->first();
        }

        // Posición: en un cambio ocupa el lugar del que sale; si no, la de su último partido
        $position = $outgoing && $outgoing->pos_x !== null
            ? ['pos_x' => $outgoing->pos_x, 'pos_y' => $outgoing->pos_y]
            : (GameItem::lastPositions([(int) $request->player_id], (int) $request->game_id)[(int) $request->player_id] ?? []);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        $gameItemNew = GameItem::create(array_merge(
            $request->only(['player_id', 'game_id', 'entered_in']),
            $position
        ));

        // Si es cambio
        if ($outgoing) {
            $outgoing->update(['change_player_id' => $gameItemNew->player_id]);
        }
    }

    // Posición de un jugador en la cancha (null vuelve a la automática)
    public function position(Request $request, int $gameitem_id)
    {
        $data = $request->validate([
            'x' => 'nullable|numeric|between:0,100|required_with:y',
            'y' => 'nullable|numeric|between:0,100|required_with:x',
        ]);

        GameItem::findOrFail($gameitem_id)->update([
            'pos_x' => $data['x'] ?? null,
            'pos_y' => $data['y'] ?? null,
        ]);

        return response()->noContent();
    }

    // Posiciones de varios jugadores del mismo partido (formación completa o restablecer)
    public function positions(Request $request, Game $game)
    {
        $data = $request->validate([
            'positions' => 'required|array|min:1|max:40',
            'positions.*.id' => 'required|integer|distinct',
            'positions.*.x' => 'nullable|numeric|between:0,100',
            'positions.*.y' => 'nullable|numeric|between:0,100',
        ]);

        $items = GameItem::where('game_id', $game->id)
            ->whereIn('id', array_column($data['positions'], 'id'))
            ->get()
            ->keyBy('id');

        // Todas deben pertenecer a este partido
        abort_if($items->count() !== count($data['positions']), 422, 'Hay jugadores que no pertenecen a este partido.');

        DB::transaction(function () use ($data, $items) {
            foreach ($data['positions'] as $position) {
                $hasBoth = isset($position['x'], $position['y']);
                $items[$position['id']]->update([
                    'pos_x' => $hasBoth ? $position['x'] : null,
                    'pos_y' => $hasBoth ? $position['y'] : null,
                ]);
            }
        });

        return response()->noContent();
    }

    public function update(Request $request, int $gameitem_id)
    {
        $request->validate([
            'goals' => 'nullable|integer|min:0|max:50',
            'santion' => 'nullable|string|in:amarilla,roja',
            'card_black' => 'nullable|boolean',
            'note' => 'nullable|string|max:500',
        ]);

        $gameItem = GameItem::findOrFail($gameitem_id);
        // SEGURIDAD: Usar only() para prevenir mass assignment - solo campos de goles y tarjetas
        $gameItem->update($request->only(['goals', 'santion', 'card_black', 'note']));
    }

    // Para pagos
    public function patch(Request $request, int $gameitem_id)
    {
        $request->validate([
            'patch' => 'required|string|in:santion,black',
        ]);

        $gameItem = GameItem::findOrFail($gameitem_id);

        $carbon = Carbon::now();
        if ($request->patch === 'santion') {
            $gameItem->update(['paid_santion' => $carbon->toDateString()]);
        } else {
            $gameItem->update(['paid_black' => $carbon->toDateString()]);
        }

        return back();
    }

    public function getPlayers(Request $request, Game $game)
    {
        // SEGURIDAD: Validar que type solo sea 1 o 2 para prevenir acceso a propiedades arbitrarias
        $request->validate([
            'type' => 'required|in:1,2',
        ]);

        // La consulta debe:
        // EL juador no debe estar jugando
        $players = Player::select(
            'players.id',
            'first_name',
            'last_name',
            'photo',
            't_shirt',
            DB::raw('(SELECT santion FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS santion'),
            DB::raw('(SELECT gi.id FROM game_items AS gi WHERE player_id = players.id AND santion IS NOT NULL AND paid_santion IS NULL LIMIT 1) AS gi_santion_id'),
            DB::raw('(SELECT card_black FROM game_items AS gi WHERE player_id = players.id AND card_black = true AND paid_black IS NULL LIMIT 1) AS black'),
            DB::raw('(SELECT gi.id FROM game_items AS gi WHERE player_id = players.id AND card_black = true AND paid_black IS NULL LIMIT 1) AS gi_black_id')
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
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => $players]);
    }
}
