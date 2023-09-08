<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
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
}
