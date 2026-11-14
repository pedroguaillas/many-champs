<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Queries\GameSummaries;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Calendario público: los partidos de un día en todas las categorías.
 */
class CalendarController extends Controller
{
    public function __invoke(Team $championship, ?string $date = null): Response
    {
        $day = $this->day($date);

        return Inertia::render('Public/Calendar', [
            'championship' => $championship->only('name', 'slug'),
            'date' => $day->toDateString(),
            'previous' => $day->copy()->subDay()->toDateString(),
            'next' => $day->copy()->addDay()->toDateString(),
            'games' => GameSummaries::forChampionship($championship->id)
                ->whereDate('games.date', $day->toDateString())
                ->orderBy('games.time')
                ->get(),
            'og' => [
                'title' => 'Calendario · ' . $championship->name,
                'description' => 'Partidos del ' . $day->format('d/m/Y') . ' de ' . $championship->name . '.',
            ],
        ]);
    }

    /** Una fecha mal escrita en la URL cae en hoy */
    private function day(?string $date): Carbon
    {
        if ($date !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('!Y-m-d', $date);
            } catch (\Exception) {
            }
        }

        return Carbon::today();
    }
}
