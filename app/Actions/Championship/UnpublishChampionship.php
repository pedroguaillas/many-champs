<?php

namespace App\Actions\Championship;

use App\Models\Team;

/**
 * Deja de compartir un campeonato: sus páginas públicas responden 404.
 * El slug se conserva por si se vuelve a compartir.
 */
class UnpublishChampionship
{
    public function handle(Team $team): Team
    {
        $team->forceFill(['is_public' => false])->save();

        return $team;
    }
}
