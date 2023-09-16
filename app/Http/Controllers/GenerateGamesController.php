<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\Group;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class GenerateGamesController extends Controller
{
    public function index(Category $category)
    {
        return Inertia::render('Game/GenerateGames', compact('category'));
    }

    public function generate(Request $request, Category $category)
    {
        // Verificar que todos los equipos de esa categoria pertenezcan a un grupo
        // Pedir que seleccionen un dia especifico
        // Pedir que seleccionen una hora especifico
        // Por el momento solo generar los partidos de IDA
        // Posterior si hay algun requerimiento de algun cliente 
        // generar los partidos de vuelta

        $groups = Group::where('category_id', $category->id)->get();

        // Recorre todos los grupos de esa categoria
        foreach ($groups as $group) {
            $this->generateByGroup($group->id, $request->date, $request->time);
        }

        return redirect(route('games.index', $category->id));
    }

    //Genera el horario del grupo seleccionado
    private function generateByGroup($group_id, $date, $hours)
    {
        $equipos = Club::select('clubs.id', 'clubs.group_id AS grupo')
            ->where('group_id', $group_id)
            ->orderBy('grupo')->get();

        $fechaJornada = Carbon::createFromFormat('Y-m-d', $date)->toDateString();

        $array = array();
        $grupo = array();

        for ($k = 0; $k < count($equipos); $k++) {

            array_push($grupo, $equipos[$k]);

            if ($k === count($equipos) - 1 || ($equipos[$k]->grupo !== $equipos[$k + 1]->grupo)) {
                if (count($grupo) % 2 !== 0) {
                    $auxEquipo = [
                        'id' => 0,
                        'gruop_id' => $grupo[count($grupo) - 1]->grupo
                    ];
                    $auxEquipo = json_encode($auxEquipo);
                    $auxEquipo = json_decode($auxEquipo);
                    array_push($grupo, $auxEquipo);
                }

                for ($i = 0; $i < count($grupo) - 1; $i++) {
                    for ($j = 0; $j < count($grupo) / 2; $j++) {
                        $objeto = [
                            'club1_id' => $grupo[$j]->id,
                            'club2_id' => $grupo[count($grupo) - 1 - $j]->id,
                            'date' => $fechaJornada,
                            'time' => $hours,
                        ];
                        array_push($array, $objeto);
                    }
                    $grupo = $this->rotate($grupo);
                    // $fechaJornada->addDays(7);
                }
                $grupo = array();
            }
        }
        $auxArray = array();
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i]['club2_id'] !== 0) {
                array_push($auxArray, $array[$i]);
            }
        }

        Progress::first()->games()->createMany($auxArray);
    }

    private function rotate($array)
    {
        $auxArray = array(); // Nuevo array a ser rotado
        $aux = $array[0]; // Asignacion en aux el primer elemento

        for ($i = 0; $i < count($array) - 2; $i++) {
            // $auxArray[$i] = $array[$i + 1];
            // Pasando los valores al nuevo array en una posicion menos uno
            array_push($auxArray, $array[$i + 1]);
        }

        // Asignacion de aux en la ultima posicion
        array_push($auxArray, $aux);

        // Asignacion de la ultima posicion del array anterior
        // para mantener siempre al ultimo
        array_push($auxArray, $array[count($array) - 1]);

        // Retorno del nuevo array
        return $auxArray;
    }
}
