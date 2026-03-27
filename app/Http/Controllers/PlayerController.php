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
            'phone' => 'nullable|max:10',
            'club_id' => 'required|integer|exists:clubs,id',
            // SEGURIDAD: Validar tipo de archivo y tamaño para prevenir upload de archivos maliciosos
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        $inputs = $request->only(['cedula', 'first_name', 'last_name', 'date_of_birth', 't_shirt', 'phone', 'club_id']);

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            // SEGURIDAD: Usar nombre generado sin la extensión original del cliente
            $imagename = uniqid() . '.' . $image->guessExtension();
            $image->storeAs('avatars', $imagename, 'public');

            $inputs['photo'] = $imagename;
        }

        $user = Auth::user();

        // Agregar el id del Campeonato actual donde se encuentra logeado el usuario
        $inputs['team_id'] = $user->currentTeam->id;

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

        // SEGURIDAD: Usar only() para prevenir mass assignment
        $player->update($request->only(['cedula', 'first_name', 'last_name', 'date_of_birth', 't_shirt', 'phone']));
    }

    public function destroy(Player $player)
    {
        $player->delete();
    }
}
