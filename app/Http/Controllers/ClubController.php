<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index(Category $category)
    {
        $query = Club::select('g.name AS gname', 'clubs.*')
            // se agrega las siguientes columnas para anular
            ->addSelect([
                'game_id' => Game::select('games.id')
                    ->whereColumn('club1_id', 'clubs.id')
                    ->orWhereColumn('club2_id', 'clubs.id')
                    ->limit(1)
            ])
            ->addSelect([
                'player_id' => Player::select('players.id')
                    ->whereColumn('club_id', 'clubs.id')
                    ->limit(1)
            ])
            ->addSelect([
                'payment_id' => Payment::select('payments.id')
                    ->whereColumn('club_id', 'clubs.id')
                    ->limit(1)
            ])

            ->where('clubs.category_id', $category->id);

        $search = FacadesRequest::exists('search') ? FacadesRequest::only('search') : null;

        if ($search !== null && $search !== '' && $search['search'] !== null) {
            $query->join('groups AS g', function ($query) use ($search) {
                $query->on('g.id', 'group_id')
                    ->where('g.name', $search);
            });
        } else {
            $query->leftJoin('groups AS g', 'g.id', 'group_id');
        }

        $clubs = $query->get();

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
        // Si el club ya esta registrado en un partido

        $gamesCount = Game::where('club1_id', $club->id)
            ->orWhere('club2_id', $club->id)
            ->count();

        if ($gamesCount) {
            return response()->json(
                ['msm' => 'No se puede eliminar el club ' . $club->name . ' ya que esta registrado en un partido'],
                422
            );
        }

        // Si el club ya tiene pagos

        $payments = Payment::where('club_id', $club->id)->count();

        if ($payments) {
            return response()->json(
                ['msm' => 'No se puede eliminar el club ' . $club->name . ' ya tienes pagos'],
                422
            );
        }

        // Si el club ya tiene jugadores

        $players = Player::where('club_id', $club->id)->count();

        if ($players) {
            return response()->json(
                ['msm' => 'No se puede eliminar el club ' . $club->name . ' ya tienes jugadores'],
                422
            );
        }

        $club->delete();
    }
}
