<?php

namespace Tests\Feature\Domain;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Progress;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tabla de posiciones, tabla de goleadores y sanciones.
 */
class StatisticsTest extends DomainTestCase
{
    public function test_position_table_computes_standings_per_group(): void
    {
        $groupA = $this->makeGroup('A');
        $groupB = $this->makeGroup('B');
        $x = $this->makeClub(['group_id' => $groupA->id, 'name' => 'X']);
        $y = $this->makeClub(['group_id' => $groupA->id, 'name' => 'Y']);
        $z = $this->makeClub(['group_id' => $groupA->id, 'name' => 'Z']);
        $q = $this->makeClub(['group_id' => $groupA->id, 'name' => 'Q']); // sin partidos
        $w = $this->makeClub(['group_id' => $groupB->id, 'name' => 'W']);
        $v = $this->makeClub(['group_id' => $groupB->id, 'name' => 'V', 'extra_points' => 3]);
        $this->makeClub(['group_id' => $groupB->id, 'name' => 'U', 'extra_points' => 4]); // sin partidos

        $this->playGame($x, $y, 2, 1);
        $this->playGame($x, $z, 1, 1);
        $this->playGame($y, $z, 0, 0);
        $this->playGame($w, $v, 3, 0);

        // No cuentan: partido sin finalizar y partido de otra fase
        $this->playGame($x, $z, 5, 0, state: 'jugando');
        $this->playGame($y, $x, 9, 0, progressId: Progress::skip(1)->first()->id);

        $this->get(route('tabla-de-posiciones.index', $this->category->id))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->component('PositionTable')->has('clubs', 7);

                $clubs = collect($page->toArray()['props']['clubs'])
                    ->map(fn ($c) => [
                        $c['name'], $c['index'], $c['ganado'], $c['empate'], $c['perdido'],
                        (int) $c['golf'], (int) $c['golc'],
                    ])->all();

                $this->assertSame([
                    // nombre, posicion, G, E, P, GF, GC
                    ['X', 1, 1, 1, 0, 3, 2],
                    ['Z', 2, 0, 2, 0, 1, 1],
                    ['Y', 3, 0, 1, 1, 1, 2],
                    ['Q', 4, 0, 0, 0, 0, 0],
                    // Grupo B: puntos = G*3 + E + extra; empate en puntos se define por diferencia de goles
                    ['U', 1, 0, 0, 0, 0, 0], // 4 pts (solo extra)
                    ['W', 2, 1, 0, 0, 3, 0], // 3 pts, +3
                    ['V', 3, 0, 0, 1, 0, 3], // 3 pts (extra), -3
                ], $clubs);
            });
    }

    public function test_position_table_without_games_lists_clubs(): void
    {
        $this->makeClub(['group_id' => $this->makeGroup('A')->id]);

        $this->get(route('tabla-de-posiciones.index', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('clubs', 1)
                ->where('clubs.0.index', 1)
            );
    }

    public function test_kickers_table_sums_goals_and_orders_desc(): void
    {
        $club1 = $this->makeClub(['name' => 'Leones']);
        $club2 = $this->makeClub(['name' => 'Tigres']);
        $ana = $this->makePlayers($club1, 1, ['first_name' => 'Ana', 'last_name' => 'Paz'])->first();
        $bea = $this->makePlayers($club2, 1, ['first_name' => 'Bea', 'last_name' => 'Ruiz'])->first();

        $g1 = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        $g2 = Game::factory()->create(['club1_id' => $club2->id, 'club2_id' => $club1->id]);
        GameItem::factory()->create(['game_id' => $g1->id, 'player_id' => $ana->id, 'goals' => 1]);
        GameItem::factory()->create(['game_id' => $g2->id, 'player_id' => $ana->id, 'goals' => 1]);
        GameItem::factory()->create(['game_id' => $g1->id, 'player_id' => $bea->id, 'goals' => 4]);

        // Goleador de otra categoria no aparece
        $other = Category::factory()->create(['team_id' => $this->team->id]);
        $otherGame = Game::factory()->create(['club1_id' => $this->makeClub([], $other)->id]);
        GameItem::factory()->create(['game_id' => $otherGame->id, 'goals' => 10]);

        $this->get(route('tabla-de-goleadores.index', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('KickersTable')
                ->has('players.data', 2)
                ->where('players.data.0.first_name', 'Bea')
                ->where('players.data.0.club', 'Tigres')
                ->where('players.data.0.goals', fn ($v) => (int) $v === 4)
                ->where('players.data.1.first_name', 'Ana')
                ->where('players.data.1.goals', fn ($v) => (int) $v === 2)
            );
    }

    public function test_sanctions_lists_only_unpaid_of_current_championship(): void
    {
        $club1 = $this->makeClub(['name' => 'Leones']);
        $club2 = $this->makeClub(['name' => 'Tigres']);
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        [$ana, $bea, $carla, $dora] = $this->makePlayers($club1, 4)->all();
        $ana->update(['first_name' => 'Ana']);
        $bea->update(['first_name' => 'Bea']);

        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $ana->id, 'santion' => 'roja']);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $bea->id, 'card_black' => true]);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $carla->id, 'santion' => 'amarilla', 'paid_santion' => now()]);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $dora->id]);

        // Sancion de otro campeonato
        $otherTeam = User::factory()->withPersonalTeam()->create()->ownedTeams()->first();
        $foreign = Game::factory()->create([
            'club1_id' => Club::factory()->create([
                'category_id' => Category::factory()->create(['team_id' => $otherTeam->id])->id,
            ])->id,
        ]);
        GameItem::factory()->create(['game_id' => $foreign->id, 'santion' => 'roja']);

        $this->get(route('santions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Santions')
                ->has('sanctions', 2)
                ->where('sanctions.0.first_name', 'Ana')
                ->where('sanctions.0.santion', 'roja')
                ->where('sanctions.0.c1name', 'Leones')
                ->where('sanctions.1.first_name', 'Bea')
            );

        $this->get(route('santions.index', 'Be'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('sanctions', 1)
                ->where('sanctions.0.first_name', 'Bea')
            );
    }

    public function test_sanctions_expose_player_club_and_boolean_black_card(): void
    {
        $club1 = $this->makeClub(['name' => 'Leones']);
        $club2 = $this->makeClub(['name' => 'Tigres']);
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        $player = $this->makePlayers($club1)->first();
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $player->id, 'card_black' => true]);

        // La vista usa player_club_id para mostrar el club del jugador y card_black como booleano
        $this->get(route('santions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('sanctions.0.player_club_id', $club1->id)
                ->where('sanctions.0.c1id', $club1->id)
                ->where('sanctions.0.card_black', true)
            );
    }

    public function test_sanction_and_black_card_can_be_paid(): void
    {
        $game = Game::factory()->create(['club1_id' => $this->makeClub()->id]);
        $item = GameItem::factory()->create(['game_id' => $game->id, 'santion' => 'roja', 'card_black' => true]);

        $this->patch(route('gameitems.patch', $item->id), ['patch' => 'santion'])->assertRedirect();
        $this->assertSame(now()->toDateString(), $item->fresh()->paid_santion->toDateString());
        $this->assertNull($item->fresh()->paid_black);

        $this->patch(route('gameitems.patch', $item->id), ['patch' => 'black'])->assertRedirect();
        $this->assertSame(now()->toDateString(), $item->fresh()->paid_black->toDateString());

        $this->patch(route('gameitems.patch', $item->id), ['patch' => 'otro'])
            ->assertSessionHasErrors('patch');
    }

    private function playGame(Club $home, Club $away, int $goalsHome, int $goalsAway, string $state = 'finalizado', ?int $progressId = null): Game
    {
        $game = Game::factory()->create([
            'club1_id' => $home->id,
            'club2_id' => $away->id,
            'state' => $state,
            'progress_id' => $progressId ?? Progress::first()->id,
        ]);

        // Un jugador por club con los goles del partido
        foreach ([[$home, $goalsHome], [$away, $goalsAway]] as [$club, $goals]) {
            GameItem::factory()->create([
                'game_id' => $game->id,
                'player_id' => $this->makePlayers($club)->first()->id,
                'goals' => $goals,
            ]);
        }

        return $game;
    }
}
