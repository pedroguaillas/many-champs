<?php

namespace App\Http\Controllers;

use App\Actions\Championship\PublishChampionship;
use App\Actions\Championship\UnpublishChampionship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Compartir o dejar de compartir el campeonato actual. Solo su dueño.
 */
class ChampionshipSharingController extends Controller
{
    public function store(Request $request, PublishChampionship $publish): RedirectResponse
    {
        $publish->handle($this->ownedTeam($request));

        return back();
    }

    public function destroy(Request $request, UnpublishChampionship $unpublish): RedirectResponse
    {
        $unpublish->handle($this->ownedTeam($request));

        return back();
    }

    private function ownedTeam(Request $request)
    {
        $team = $request->user()->currentTeam;

        abort_unless($team && $request->user()->ownsTeam($team), 403);

        return $team;
    }
}
