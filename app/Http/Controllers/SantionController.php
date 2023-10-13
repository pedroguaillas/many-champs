<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SantionController extends Controller
{
    public function index($search = null)
    {
        $query = 'select game_items.*, p.club_id as player_club_id, c1.id as c1id, c2.id as c2id, c1.name as c1name,';
        $query .= 'c2.name as c2name, first_name, last_name, c.name as category_name from game_items inner join games as g on ';
        $query .= 'g.id = game_id inner join players as p on p.id = player_id inner join clubs as c1 on c1.id = club1_id inner ';
        $query .= 'join clubs as c2 on c2.id = club2_id inner join categories as c on c.id = c1.category_id where ((santion is not ';
        // Mostrar solo los que no estan cobrados
        $query .= "null and paid_santion is null) or (card_black = 1 and paid_black is null)) and first_name LIKE '%$search%'";
        $query .= " order by first_name asc";

        $sanctions = DB::select($query);
        $sanctions = json_decode(json_encode($sanctions));

        return Inertia::render('Santions', compact('sanctions'));
    }
}
