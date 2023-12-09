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

        return Inertia::render('Dashboard', compact('players', 'clubs', 'games', 'sanctions'));
    }

    public function sendRedirect($type)
    {
        $user = Auth::user();

        $categories = DB::table('categories AS c')
            ->select(DB::raw('c.id,c.name,COUNT(clubs.id) AS count'))
            ->leftJoin('clubs', 'c.id', 'category_id')
            ->groupBy('id', 'name')
            // Del campeonato(team_id) actual donde se encuentra logeado el usuario
            ->where('team_id', $user->currentTeam->id)
            ->get();

        $type = $type === 'partidos' ? 'games' : $type;
        return Inertia::render('SelectCategory', compact('categories', 'type'));
    }

    public function calendar($date = null)
    {
        $currentTeamId = Auth::user()->currentTeam->id;

        if ($date === null) {
            $date = Carbon::now()->toDateString();
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
            ->where('card_black', 1)
            ->whereDate('paid_black', $date)
            ->count();

        return Inertia::render('Diary', compact('payments', 'cardred', 'cardyellow', 'cardblack'));
    }
}
