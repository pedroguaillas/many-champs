<?php

namespace App\Actions\Championship;

use App\Models\Team;
use Illuminate\Support\Str;

/**
 * Hace público un campeonato. La primera vez le crea el slug de su enlace;
 * después se conserva aunque cambie el nombre, para no romper enlaces compartidos.
 */
class PublishChampionship
{
    public function handle(Team $team): Team
    {
        $team->forceFill([
            'slug' => $team->slug ?: $this->uniqueSlug($team),
            'is_public' => true,
        ])->save();

        return $team;
    }

    private function uniqueSlug(Team $team): string
    {
        $base = Str::limit(Str::slug($team->name), 100, '') ?: 'campeonato';
        $slug = $base;

        for ($i = 2; Team::where('slug', $slug)->whereKeyNot($team->id)->exists(); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }
}
