<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Payment;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Del campeonato(team_id) actual donde se encuentra logeado el usuario
        $currentTeamId = Auth::user()->currentTeam->id;

        $players = Player::where('team_id', $currentTeamId)->count();

        $clubs = Club::join('categories AS c', function ($query) use ($currentTeamId) {
            $query->on('c.id', 'category_id')
                ->where('team_id', $currentTeamId);
        })->count();

        $games = Game::join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->join('categories AS c', function ($query) use ($currentTeamId) {
                $query->on('c.id', 'category_id')
                    ->where('team_id', $currentTeamId);
            })->count();

        // Falta restringir para nosotros las targetas negras
        $sanctions = GameItem::join('players AS p', function ($query) use ($currentTeamId) {
            $query->on('p.id', 'player_id')
                ->where('team_id', $currentTeamId);
        })->whereNotNull('santion')->count();

        // Guía de puesta en marcha: qué pasos del torneo ya están hechos
        $categories = DB::table('categories')->where('team_id', $currentTeamId)->count();
        $groups = DB::table('groups AS gr')
            ->join('categories AS c', 'c.id', 'gr.category_id')
            ->where('c.team_id', $currentTeamId)
            ->count();

        // Partidos en juego y los próximos por jugar
        $agenda = fn () => Game::select('games.id', 'games.state', 'games.date', 'games.time', 'c1.name AS c1name', 'c2.name AS c2name', 'c1.color AS c1color', 'c2.color AS c2color', 'c.name AS category')
            ->join('clubs AS c1', 'games.club1_id', 'c1.id')
            ->join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->join('categories AS c', 'c.id', 'c1.category_id')
            ->where('c.team_id', $currentTeamId);

        $live = $agenda()->where('games.state', 'jugando')->orderBy('games.date')->orderBy('games.time')->limit(5)->get();
        $upcoming = $agenda()->whereIn('games.state', ['creado', 'planificado'])
            ->whereDate('games.date', '>=', Carbon::today())
            ->orderBy('games.date')->orderBy('games.time')->limit(5)->get();

        return Inertia::render('Dashboard', compact('players', 'clubs', 'games', 'sanctions', 'categories', 'groups', 'live', 'upcoming'));
    }

    public function sendRedirect(Request $request, $type)
    {
        // SEGURIDAD: Validar que type sea un valor permitido para prevenir inyección en el frontend
        $allowedTypes = ['clubs', 'partidos', 'games', 'grupos', 'tabla-de-posiciones', 'tabla-de-goleadores'];
        abort_if(!in_array($type, $allowedTypes), 404);

        $type = ['partidos' => 'games', 'grupos' => 'groups'][$type] ?? $type;

        $user = Auth::user();

        // Resumen por categoría para decidir rápido a cuál entrar
        $gamesOf = 'FROM games AS g JOIN clubs AS gc ON gc.id = g.club1_id WHERE gc.category_id = c.id';
        $categories = DB::table('categories AS c')
            ->select('c.id', 'c.name', 'c.gender')
            ->selectRaw('(SELECT COUNT(*) FROM clubs WHERE clubs.category_id = c.id) AS count')
            ->selectRaw('(SELECT COUNT(*) FROM players AS p JOIN clubs AS pc ON pc.id = p.club_id WHERE pc.category_id = c.id) AS players')
            ->selectRaw("(SELECT COUNT(*) $gamesOf) AS games")
            ->selectRaw("(SELECT COUNT(*) $gamesOf AND g.state = 'finalizado') AS finished")
            ->selectRaw("(SELECT COUNT(*) $gamesOf AND g.state = 'jugando') AS live")
            ->selectRaw("(SELECT MIN(CONCAT(g.date, ' ', g.time)) $gamesOf AND g.state IN ('creado', 'planificado') AND g.date >= CURRENT_DATE) AS next_game")
            // Del campeonato(team_id) actual donde se encuentra logeado el usuario
            ->where('c.team_id', $user->currentTeam->id)
            ->orderBy('c.name')
            ->get();

        // Con una sola categoría no hay nada que elegir: entrar directo
        // (?todas=1 muestra la lista igual, para "Cambiar categoría")
        if ($categories->count() === 1 && !$request->boolean('todas')) {
            return to_route("{$type}.index", $categories->first()->id);
        }

        return Inertia::render('SelectCategory', compact('categories', 'type'));
    }

    public function calendar($date = null)
    {
        $currentTeamId = Auth::user()->currentTeam->id;

        // SEGURIDAD: Validar formato de fecha para prevenir inyección
        if ($date === null) {
            $date = Carbon::now()->toDateString();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date)->toDateString();
            } catch (\Exception $e) {
                $date = Carbon::now()->toDateString();
            }
        }

        $games = Game::selectRaw('games.id,c1.name AS c1name,c2.name AS c2name,games.state,games.date,games.time,c.name,g.name AS g_name')
            ->join('clubs AS c1', 'games.club1_id', 'c1.id')
            ->join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->leftJoin('groups AS g', 'c2.group_id', 'g.id')
            ->join('categories AS c', function ($query) use ($currentTeamId) {
                $query->on('c.id', 'c2.category_id')
                    ->where('team_id', $currentTeamId);
            })
            // ->join('groups AS g', 'g.category_id', 'c.id')
            // Si date es diferente de nulo permitir
            ->where('date', $date)
            ->orderByDesc('date')
            ->orderBy('time')
            // ->groupBy('gname')
            ->get();

        return Inertia::render('Calendar', compact('games', 'date'));
    }

    public function diary()
    {
        $user = Auth::user();
        $date = Carbon::now()->toDateString();

        $payments = Payment::join('clubs AS c', 'c.id', 'club_id')
            ->join('categories AS ca', function ($query) use ($user) {
                $query->on('ca.id', 'category_id')
                    ->where('team_id', $user->currentTeam->id);
            })
            ->whereDate('payments.created_at', $date)
            ->get();

        $cardred = GameItem::join('players AS p', 'p.id', 'player_id')
            ->where('santion', 'roja')
            ->whereDate('paid_santion', $date)
            ->count();

        $cardyellow = GameItem::join('players AS p', 'p.id', 'player_id')
            ->where('santion', 'amarilla')
            ->whereDate('paid_santion', $date)
            ->count();

        $cardblack = GameItem::join('players AS p', 'p.id', 'player_id')
            ->where('card_black', true)
            ->whereDate('paid_black', $date)
            ->count();

        return Inertia::render('Diary', compact('payments', 'cardred', 'cardyellow', 'cardblack'));
    }
}
