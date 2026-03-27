<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SantionController extends Controller
{
    public function index($search = null)
    {
        $user = Auth::user();

        // SEGURIDAD: Se reemplazó la interpolación directa de variables en SQL
        // por consultas parametrizadas para prevenir SQL Injection (OWASP A03:2021)
        $sanctions = DB::select(
            'SELECT game_items.*, p.club_id AS player_club_id, c1.id AS c1id, c2.id AS c2id, c1.name AS c1name, '
            . 'c2.name AS c2name, first_name, last_name, c.name AS category_name '
            . 'FROM game_items '
            . 'INNER JOIN games AS g ON g.id = game_id '
            . 'INNER JOIN players AS p ON p.id = player_id '
            . 'INNER JOIN clubs AS c1 ON c1.id = club1_id '
            . 'INNER JOIN clubs AS c2 ON c2.id = club2_id '
            . 'INNER JOIN categories AS c ON c.id = c1.category_id AND c.team_id = ? '
            . 'WHERE ((santion IS NOT NULL AND paid_santion IS NULL) OR (card_black = 1 AND paid_black IS NULL)) '
            . 'AND first_name LIKE ? '
            . 'ORDER BY first_name ASC',
            [$user->currentTeam->id, '%' . ($search ?? '') . '%']
        );

        $sanctions = json_decode(json_encode($sanctions));

        return Inertia::render('Santions', compact('sanctions'));
    }
}
