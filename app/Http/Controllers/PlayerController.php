<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index(Club $club)
    {
        $players = $club->players;
        return Inertia::render('Player/Index', compact('club', 'players'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'nullable|max:10',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'date_of_birth' => 'nullable|date',
            't_shirt' => 'nullable|numeric',
            'phone' => 'nullable|max:10'
        ]);

        $inputs = $request->except('photo');

        if ($request->photo !== null) {
            $image = $request->file('photo');
            $imagename = uniqid() . '.' . $image->getClientOriginalExtension();
            $request->file('photo')->storeAs('avatars', $imagename, 'public');

            $inputs += ['photo' => $imagename];
        }

        $user = Auth::user();

        // Agregar el id del Campeonato actual donde se encuentra logeado el usuario
        $inputs += ['team_id' => $user->currentTeam->id];

        Player::create($inputs);
    }

    public function update(Request $request, Player $player)
    {
        $request->validate([
            'cedula' => 'nullable|max:10',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'date_of_birth' => 'nullable|date',
            't_shirt' => 'nullable|numeric',
            'phone' => 'nullable|max:10'
        ]);

        $player->update($request->all());
    }

    public function destroy(Player $player)
    {
        $player->delete();
    }
}
