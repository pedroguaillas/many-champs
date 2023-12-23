<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Player;
use Inertia\Inertia;

class KickersTableController extends Controller
{
    public function index(Category $category)
    {
        $currentTeamId = Auth::user()->currentTeam->id;

        $players = Player::selectRaw('first_name, last_name, c.name AS club, SUM(goals) AS goals')
            ->join('clubs AS c', function ($query) use ($category) {
                $query->on('c.id', 'club_id')
                    ->where('category_id', $category->id);
            })
            ->join('game_items AS gi', 'gi.player_id', 'players.id')
            ->where('team_id', $currentTeamId)
            ->groupBy('first_name', 'last_name', 'club')
            ->orderBy('goals', 'desc')
            ->paginate();

        return Inertia::render('KickersTable', compact('category', 'players'));
    }
}
