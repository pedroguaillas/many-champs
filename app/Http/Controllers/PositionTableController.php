<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Progress;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PositionTableController extends Controller
{
    public function index(Category $category)
    {
        $progress = Progress::first();

        $query = "SELECT c.id AS club_id,c.name,c.extra_points,c.group_id,groups.name AS group_name,club_game.club1_id,club_game.club2_id,club_game.goles1,club_game.goles2 FROM clubs AS c ";
        $query .= "LEFT JOIN (SELECT club1_id,club2_id,(SELECT SUM(goals) FROM game_items AS gi WHERE gi.game_id=g.id AND gi.player_id IN (SELECT id FROM players WHERE ";
        $query .= "club_id=club1_id)) AS goles1, (SELECT SUM(goals) FROM game_items AS gi WHERE gi.game_id=g.id AND gi.player_id IN (SELECT id FROM players WHERE club_id=club2_id)) ";
        $query .= "AS goles2 FROM games AS g WHERE g.state='finalizado' AND g.progress_id = $progress->id) AS club_game ON c.id=club_game.club1_id OR c.id=club_game.club2_id ";
        $query .= "LEFT JOIN groups ON groups.id=c.group_id WHERE c.category_id=" . $category->id . " ORDER BY c.id";

        $games = DB::select($query);

        $i = 0;
        // valores anteriores de clubes
        $club_id = 0;
        $name = '';
        $extra_points = 0;
        $group_id = 0;
        $group_name = '';
        // Contadores
        $ganado = 0;
        $empate = 0;
        $perdido = 0;
        // Sumadores
        $golf = 0;
        $golc = 0;

        $clubs = [];

        foreach ($games as $game) {
            if ($club_id !== $game->club_id) {
                if ($i > 0) {
                    array_push($clubs, [
                        'club_id' => $club_id,
                        'name' => $name,
                        'extra_points' => $extra_points,
                        'group_id' => $group_id,
                        'group_name' => $group_name,
                        'ganado' => $ganado,
                        'empate' => $empate,
                        'perdido' => $perdido,
                        'golf' => $golf,
                        'golc' => $golc
                    ]);
                }
                // Reiniciar sumadores
                $ganado = $game->club_id === $game->club1_id ? ($game->goles1 > $game->goles2 ? 1 : 0) : ($game->goles2 > $game->goles1 ? 1 : 0);
                $empate = $game->goles1 !== null || $game->goles2 !== null ? ($game->goles1 === $game->goles2 ? 1 : 0) : 0;
                $perdido = $game->club_id === $game->club1_id ? ($game->goles1 < $game->goles2 ? 1 : 0) : ($game->goles2 < $game->goles1 ? 1 : 0);
                // Reiniciar contadores
                $golf = $game->club_id === $game->club1_id ? $game->goles1 : $game->goles2;
                $golc = $game->club_id === $game->club1_id ? $game->goles2 : $game->goles1;
            } else {
                // Adicionar 1 si cumple la condicion
                $ganado += $game->club_id === $game->club1_id ? ($game->goles1 > $game->goles2 ? 1 : 0) : ($game->goles2 > $game->goles1 ? 1 : 0);
                $empate += $game->goles1 !== null || $game->goles2 !== null ? ($game->goles1 === $game->goles2 ? 1 : 0) : 0;
                $perdido += $game->club_id === $game->club1_id ? ($game->goles1 < $game->goles2 ? 1 : 0) : ($game->goles2 < $game->goles1 ? 1 : 0);
                // Adicionar los goles si cumple la condicion
                $golf += $game->club_id === $game->club1_id ? $game->goles1 : $game->goles2;
                $golc += $game->club_id === $game->club1_id ? $game->goles2 : $game->goles1;
            }

            $i++;
            $club_id = $game->club_id;
            $name = $game->name;
            $extra_points = $game->extra_points;
            $group_id = $game->group_id;
            $group_name = $game->group_name;
        }

        if ($i > 0) {
            array_push($clubs, [
                'club_id' => $club_id,
                'name' => $name,
                'extra_points' => $extra_points,
                'group_id' => $group_id,
                'group_name' => $group_name,
                'ganado' => $ganado,
                'empate' => $empate,
                'perdido' => $perdido,
                'golf' => $golf,
                'golc' => $golc
            ]);
        }

        $clubs = json_decode(json_encode($clubs));

        // ajustes para poner los CEROS a la izquierda
        // Grupo 2 Ceros no puede haber mas de 99 grupos
        $c = 3; // Cantidad de CEROS
        // Sumamos los puntos GANADOS * 3 + EMPATES + PUNTOS EXTRAS y con 2 CEROS no puede haber mas de 99 puntos ganados
        // Diferencia de goles empieza en 100 para restar o sumar Y con 3 CEROS
        // Clave para poner 3 ceros porque un equipo puede perder todos los partidos y no va sumar ni puntos ni goles al inverso
        $goles = 100;

        usort($clubs, function ($a, $b) use ($goles, $c) {
            return strcmp(
                str_pad($goles - $a->group_id, $c, '0', STR_PAD_LEFT) .
                    str_pad(($a->ganado * 3) + $a->empate + $a->extra_points, $c, '0', STR_PAD_LEFT) .
                    str_pad($goles + $a->golf - $a->golc, $c, '0', STR_PAD_LEFT),
                str_pad($goles - $b->group_id, $c, '0', STR_PAD_LEFT) .
                    str_pad(($b->ganado * 3) + $b->empate + $b->extra_points, $c, '0', STR_PAD_LEFT) .
                    str_pad($goles + $b->golf - $b->golc, $c, '0', STR_PAD_LEFT)
            );
        });

        $clubs = array_reverse($clubs);

        return Inertia::render('PositionTable', compact('category', 'clubs'));
    }
}
