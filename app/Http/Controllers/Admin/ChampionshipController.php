<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Events\AddingTeam;

class ChampionshipController extends Controller
{
    public function index()
    {
        $championships = Team::with('owner:id,name,email')
            ->withCount('users')
            ->where('personal_team', false)
            ->latest()
            ->get();

        return Inertia::render('Admin/Championships', compact('championships'));
    }

    public function create()
    {
        return Inertia::render('Admin/ChampionshipCreate');
    }

    public function searchUser(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first(['id', 'name', 'email']);

        return response()->json(['user' => $user]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'owner_mode' => 'required|in:existing,new',
        ];

        if ($request->owner_mode === 'existing') {
            $rules['owner_id'] = 'required|exists:users,id';
        } else {
            $rules['owner_name'] = 'required|string|max:255';
            $rules['owner_email'] = 'required|email|unique:users,email';
            $rules['owner_password'] = 'required|string|min:8';
        }

        $request->validate($rules);

        if ($request->owner_mode === 'existing') {
            $owner = User::findOrFail($request->owner_id);
        } else {
            $owner = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
            ]);

            // Crear el personal team del nuevo usuario
            $owner->ownedTeams()->create([
                'name' => $owner->name . "'s Team",
                'personal_team' => true,
            ]);
        }

        AddingTeam::dispatch($owner);

        $team = $owner->ownedTeams()->create([
            'name' => $request->name,
            'personal_team' => false,
        ]);

        $owner->switchTeam($team);

        return redirect()->route('admin.championships.index');
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($request->only('name'));

        return back();
    }

    public function destroy(Team $team)
    {
        $team->purge();

        return redirect()->route('admin.championships.index');
    }
}
