<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Group;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index(Category $category)
    {
        $query = Game::selectRaw(
            'games.id,c1.name AS c1name,c2.name AS c2name,games.state,(SELECT SUM(goals) FROM game_items WHERE games.id = game_id AND player_id IN (SELECT id FROM players WHERE club_id = club1_id)) AS gols1,(SELECT SUM(goals) FROM game_items WHERE games.id = game_id AND player_id IN (SELECT id FROM players WHERE club_id = club2_id)) AS gols2,g.id AS g_id'
        )
            ->join('clubs AS c1', 'games.club1_id', 'c1.id')
            ->join('clubs AS c2', 'games.club2_id', 'c2.id')
            ->where('c1.category_id', $category->id)
            ->orderBy('c1name', 'DESC');

        $search = FacadesRequest::exists('search') ? FacadesRequest::only('search') : null;

        if ($search !== null && $search !== '' && $search['search'] !== null) {
            $query->join('groups AS g', function ($query) use ($search) {
                $query->on('g.id', 'c1.group_id')
                    ->where('g.id', $search);
            });
        } else {
            $query->leftJoin('groups AS g', 'c1.group_id', 'g.id');
        }

        $games = $query->get();

        $groups = Group::where('category_id', $category->id)->get();

        return Inertia::render('Game/Index', compact('category', 'games', 'groups'));
    }

    public function create(Category $category)
    {
        $clubs = Club::where('category_id', $category->id)->get();
        $progress = Progress::all();

        return Inertia::render('Game/Create', compact('category', 'clubs', 'progress'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'club1_id' => 'required|different:club2_id',
            'club2_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'progress_id' => 'required'
        ]);

        Game::create($request->input());

        $club = Club::find($request->club1_id);

        return redirect(route('games.index', $club->category_id));
    }

    public function edit(Category $category, Game $game)
    {
        $club = Club::find($game->club1_id);

        $clubs = Club::where('category_id', $club->category_id)->get();

        $progress = Progress::all();

        return Inertia::render('Game/Edit', compact('category', 'clubs', 'progress', 'game'));
    }

    public function update(Request $request, Game $game)
    {
        $request->validate([
            'club1_id' => 'required|different:club2_id',
            'club2_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'progress_id' => 'required'
        ]);

        $game->update($request->input());

        $club = Club::find($request->club1_id);

        return redirect(route('games.index', $club->category_id));
    }

    public function ended(Request $request, Game $game)
    {
        $game->update($request->input());

        return redirect(route('calendar'));
    }

    public function destroy(Game $game)
    {
        GameItem::where('game_id', $game->id)->delete();
        $game->delete();
    }
}
