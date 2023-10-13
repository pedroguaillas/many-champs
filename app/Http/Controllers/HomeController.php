<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Player;
use Carbon\Carbon;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $players = Player::all()->count();
        $clubs = Club::all()->count();
        $games = Game::all()->count();
        $players = Player::all()->count();
        $sanctions = GameItem::whereNotNull('santion')->count();

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

        return Inertia::render('SelectCategory', compact('categories', 'type'));
    }

    public function calendar($date = null)
    {
        if ($date === null) {
            $date = Carbon::now()->toDateString();
        }

        $games = Game::select(DB::raw('games.id,c1.name AS c1name,c2.name AS c2name,games.state,games.date,games.time,c.name,g.name AS g_name'))
            // $games = Game::select('games.id', 'c1.name AS c1name', 'c2.name AS c2name', 'games.state', 'games.date', 'games.time', 'c.name','g.name AS gname')
            ->join('clubs AS c1', 'games.club1_id', 'c1.id')
            ->join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->leftJoin('groups AS g', 'c2.group_id', 'g.id')
            ->join('categories AS c', 'c2.category_id', 'c.id')
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
        return Inertia::render('Diary');
    }
}
