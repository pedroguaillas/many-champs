<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index(Category $category)
    {
        $clubs = Club::select('g.name AS gname', 'clubs.*')
            ->leftJoin('groups AS g', 'g.id', 'group_id')
            ->where('clubs.category_id', $category->id)->get();

        $groups = Group::where('category_id', $category->id)->get();

        return Inertia::render('Club/Index', compact('category', 'clubs', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:300',
            'address' => 'required|max:300',
            'extra_points' => 'numeric|min:0,max:20',
        ]);

        $club = new Club($request->all());
        $club->save();
    }

    public function update(Request $request, Club $club)
    {
        $request->validate([
            'name' => 'required|max:300',
            'address' => 'required|max:300',
            'extra_points' => 'numeric|min:0,max:20'
        ]);

        $club->update($request->input());
    }

    public function destroy(Club $club)
    {
        // Si el club ya esta restrado en un partido
        $gamesCount = Game::where('club1_id', $club->id)
            ->orWhere('club2_id', $club->id)
            ->count();

        if ($gamesCount) {
            return response()->json(
                ['msm' => 'No se puede eliminar el club ' . $club->name . ' ya que esta registrado en un partido'],
                422
            );
        }

        $club->delete();
    }
}
