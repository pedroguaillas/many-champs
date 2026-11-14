<?php

namespace Tests\Feature\Domain;

use App\Models\Club;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Progress;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Flujo completo de un partido:
 * crear -> seleccionar jugadores -> jugar (goles, tarjetas, cambios) -> finalizar.
 */
class GameFlowTest extends DomainTestCase
{
    public function test_games_index_lists_games_of_category_with_goals(): void
    {
        $group = $this->makeGroup('A');
        $club1 = $this->makeClub(['group_id' => $group->id, 'name' => 'Leones']);
        $club2 = $this->makeClub(['group_id' => $group->id, 'name' => 'Tigres']);
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);

        $p1 = $this->makePlayers($club1)->first();
        $p2 = $this->makePlayers($club2)->first();
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $p1->id, 'goals' => 3]);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $p2->id, 'goals' => 1]);

        // Partido de otra categoria no debe aparecer
        Game::factory()->create();

        $this->get(route('games.index', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game/Index')
                ->has('games', 1)
                ->where('games.0.c1name', 'Leones')
                ->where('games.0.c2name', 'Tigres')
                ->where('games.0.gols1', fn ($v) => (int) $v === 3)
                ->where('games.0.gols2', fn ($v) => (int) $v === 1)
                ->where('games.0.g_id', $group->id)
                ->has('groups', 1)
            );
    }

    public function test_games_index_filters_by_group(): void
    {
        $groupA = $this->makeGroup('A');
        $groupB = $this->makeGroup('B');
        $a1 = $this->makeClub(['group_id' => $groupA->id]);
        $a2 = $this->makeClub(['group_id' => $groupA->id]);
        $b1 = $this->makeClub(['group_id' => $groupB->id]);
        $b2 = $this->makeClub(['group_id' => $groupB->id]);
        Game::factory()->create(['club1_id' => $a1->id, 'club2_id' => $a2->id]);
        Game::factory()->create(['club1_id' => $b1->id, 'club2_id' => $b2->id]);

        $this->get(route('games.index', $this->category->id).'?search='.$groupB->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('games', 1)
                ->where('games.0.g_id', $groupB->id)
            );
    }

    public function test_create_page_renders(): void
    {
        $this->makeClub();

        $this->get(route('games.create', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game/Create')
                ->has('clubs', 1)
                ->has('progress', 5)
            );
    }

    public function test_game_can_be_created(): void
    {
        $club1 = $this->makeClub();
        $club2 = $this->makeClub();

        $this->post(route('games.store', $this->category->id), [
            'club1_id' => $club1->id,
            'club2_id' => $club2->id,
            'date' => '2026-05-10',
            'time' => '15:30',
            'progress_id' => Progress::first()->id,
        ])->assertRedirect(route('games.index', $this->category->id));

        $game = Game::sole();
        $this->assertSame('creado', $game->state);
        $this->assertSame('2026-05-10', $game->date->toDateString());
        $this->assertSame('15:30:00', $game->time->format('H:i:s'));
    }

    public function test_game_requires_different_clubs(): void
    {
        $club = $this->makeClub();

        $this->post(route('games.store', $this->category->id), [
            'club1_id' => $club->id,
            'club2_id' => $club->id,
            'date' => '2026-05-10',
            'time' => '15:30',
            'progress_id' => Progress::first()->id,
        ])->assertSessionHasErrors('club1_id');

        $this->assertSame(0, Game::count());
    }

    public function test_game_store_validates_required_fields(): void
    {
        $this->post(route('games.store', $this->category->id), [])
            ->assertSessionHasErrors(['club1_id', 'club2_id', 'date', 'time', 'progress_id']);
    }

    public function test_game_edit_and_update(): void
    {
        $game = Game::factory()->create(['club1_id' => $this->makeClub()->id]);

        $this->get(route('games.edit', $game->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game/Edit')
                ->where('game.id', $game->id)
                ->where('game.date', $game->date->format('Y-m-d'))
            );

        $this->put(route('games.update', $game->id), [
            'club1_id' => $game->club1_id,
            'club2_id' => $game->club2_id,
            'date' => '2026-06-01',
            'time' => '09:00',
            'progress_id' => Progress::skip(1)->first()->id,
            'state' => 'planificado',
        ])->assertRedirect(route('games.index', $this->category->id));

        $game->refresh();
        $this->assertSame('planificado', $game->state);
        $this->assertSame('2026-06-01', $game->date->toDateString());
    }

    public function test_game_update_rejects_invalid_state(): void
    {
        $game = Game::factory()->create(['club1_id' => $this->makeClub()->id]);

        $this->put(route('games.update', $game->id), [
            'club1_id' => $game->club1_id,
            'club2_id' => $game->club2_id,
            'date' => '2026-06-01',
            'time' => '09:00',
            'progress_id' => $game->progress_id,
            'state' => 'cancelado',
        ])->assertSessionHasErrors('state');
    }

    public function test_select_players_page_shows_both_clubs_with_pending_sanctions(): void
    {
        $club1 = $this->makeClub(['name' => 'Leones']);
        $club2 = $this->makeClub(['name' => 'Tigres']);
        $players1 = $this->makePlayers($club1, 3);
        $this->makePlayers($club2, 2);
        $this->app['db']->table('payments')->insert([
            ['club_id' => $club1->id, 'amount' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['club_id' => $club1->id, 'amount' => 15, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sancion pendiente de un partido anterior
        $previous = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        $sanctioned = GameItem::factory()->create([
            'game_id' => $previous->id,
            'player_id' => $players1[0]->id,
            'santion' => 'roja',
        ]);

        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);

        $this->get(route('games.select-players', $game->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Play/SelectPlayersToPlay')
                ->where('game.c1_name', 'Leones')
                ->where('game.c2_name', 'Tigres')
                ->where('game.paid1', fn ($v) => (float) $v === 25.0)
                ->where('game.paid2', null)
                ->has('club1_players', 3)
                ->has('club2_players', 2)
                ->where('club1_players', fn ($players) => collect($players)
                    ->firstWhere('id', $players1[0]->id)['santion'] === 'roja'
                    && collect($players)->firstWhere('id', $players1[0]->id)['gi_saction_id'] === $sanctioned->id)
            );
    }

    public function test_started_game_cannot_register_lineup_again(): void
    {
        [$game, $players1, $players2] = $this->startedGame();
        $items = GameItem::where('game_id', $game->id)->count();

        $this->get(route('games.select-players', $game->id))
            ->assertRedirect(route('playing.index', $game->id));

        $this->post(route('play.store', $game->id), [
            'clubs1' => [$players1->first()->id],
            'clubs2' => [$players2->first()->id],
        ])->assertRedirect(route('playing.index', $game->id));

        $this->assertSame($items, GameItem::where('game_id', $game->id)->count());
    }

    public function test_select_players_returns_404_for_missing_game(): void
    {
        $this->get(route('games.select-players', 999))->assertNotFound();
    }

    public function test_starting_game_creates_items_and_sets_state_jugando(): void
    {
        $game = Game::factory()->create(['club1_id' => $this->makeClub()->id]);
        $players1 = $this->makePlayers(Club::find($game->club1_id), 2);
        $players2 = $this->makePlayers(Club::find($game->club2_id), 2);

        $this->post(route('play.store', $game->id), [
            'clubs1' => $players1->pluck('id')->all(),
            'clubs2' => $players2->pluck('id')->all(),
        ])->assertRedirect(route('playing.index', $game->id));

        $this->assertSame('jugando', $game->fresh()->state);
        $this->assertSame(4, GameItem::where('game_id', $game->id)->where('entered_in', 'inicio')->count());
    }

    public function test_starting_game_requires_players_from_both_clubs(): void
    {
        $game = Game::factory()->create(['club1_id' => $this->makeClub()->id]);

        $this->post(route('play.store', $game->id), ['clubs1' => [], 'clubs2' => []])
            ->assertSessionHasErrors(['clubs1', 'clubs2']);

        $this->assertSame('creado', $game->fresh()->state);
    }

    public function test_playing_page_lists_players_on_field_per_club(): void
    {
        [$game, $players1, $players2] = $this->startedGame();

        $this->get(route('playing.index', $game->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Play/Playing')
                ->where('game.id', $game->id)
                ->has('c1_players', 2)
                ->has('c2_players', 2)
                ->has('c1_players.0', fn (Assert $p) => $p
                    ->hasAll(['gi_id', 'goals', 'santion', 'paid_santion', 'change_player_id', 'card_black'])
                    ->etc()
                )
            );
    }

    public function test_playing_page_returns_404_for_missing_game(): void
    {
        $this->get(route('playing.index', 999))->assertNotFound();
    }

    public function test_goals_and_cards_can_be_registered(): void
    {
        [$game, $players1] = $this->startedGame();
        $item = GameItem::where(['game_id' => $game->id, 'player_id' => $players1[0]->id])->sole();

        $this->put(route('gameitems.update', $item->id), [
            'goals' => 2,
            'santion' => 'amarilla',
            'card_black' => true,
            'note' => 'Falta fuerte',
        ])->assertOk();

        $item->refresh();
        $this->assertSame(2, $item->goals);
        $this->assertSame('amarilla', $item->santion);
        $this->assertTrue($item->card_black);
        $this->assertSame('Falta fuerte', $item->note);
    }

    public function test_game_item_update_validates_input(): void
    {
        [$game, $players1] = $this->startedGame();
        $item = GameItem::where(['game_id' => $game->id, 'player_id' => $players1[0]->id])->sole();

        $this->put(route('gameitems.update', $item->id), ['goals' => -1, 'santion' => 'azul'])
            ->assertSessionHasErrors(['goals', 'santion']);
    }

    public function test_substitution_links_outgoing_player(): void
    {
        [$game, $players1] = $this->startedGame();
        $substitute = $this->makePlayers(Club::find($game->club1_id))->first();

        $this->post(route('gameitems.store'), [
            'player_id' => $substitute->id,
            'game_id' => $game->id,
            'entered_in' => 'segundo tiempo',
            'change_player_id' => $players1[0]->id,
        ])->assertOk();

        $this->assertDatabaseHas('game_items', [
            'game_id' => $game->id,
            'player_id' => $substitute->id,
            'entered_in' => 'segundo tiempo',
        ]);
        $this->assertDatabaseHas('game_items', [
            'game_id' => $game->id,
            'player_id' => $players1[0]->id,
            'change_player_id' => $substitute->id,
        ]);
    }

    public function test_player_can_be_added_without_substitution(): void
    {
        [$game] = $this->startedGame();
        $late = $this->makePlayers(Club::find($game->club2_id))->first();

        $this->post(route('gameitems.store'), [
            'player_id' => $late->id,
            'game_id' => $game->id,
            'entered_in' => 'completar',
        ])->assertOk();

        $this->assertSame(5, GameItem::where('game_id', $game->id)->count());
    }

    public function test_player_position_can_be_saved_and_reset(): void
    {
        [$game, $players1] = $this->startedGame();
        $item = GameItem::where(['game_id' => $game->id, 'player_id' => $players1[0]->id])->sole();

        $this->patch(route('gameitems.position', $item->id), ['x' => 12.5, 'y' => 40])->assertNoContent();
        $item->refresh();
        $this->assertSame(12.5, $item->pos_x);
        $this->assertSame(40.0, $item->pos_y);

        $this->patch(route('gameitems.position', $item->id), ['x' => null, 'y' => null])->assertNoContent();
        $this->assertNull($item->fresh()->pos_x);
    }

    public function test_player_position_is_validated(): void
    {
        [$game, $players1] = $this->startedGame();
        $item = GameItem::where(['game_id' => $game->id, 'player_id' => $players1[0]->id])->sole();

        $this->patch(route('gameitems.position', $item->id), ['x' => 120, 'y' => -1])
            ->assertSessionHasErrors(['x', 'y']);
        $this->patch(route('gameitems.position', $item->id), ['x' => 50])
            ->assertSessionHasErrors(['y']);
    }

    public function test_team_positions_are_saved_in_batch(): void
    {
        [$game, $players1] = $this->startedGame();
        $items = GameItem::where('game_id', $game->id)->whereIn('player_id', $players1->pluck('id'))->get();

        $this->patch(route('gameitems.positions', $game->id), ['positions' => [
            ['id' => $items[0]->id, 'x' => 8, 'y' => 50],
            ['id' => $items[1]->id, 'x' => 30, 'y' => 25],
        ]])->assertNoContent();

        $this->assertSame(8.0, $items[0]->fresh()->pos_x);
        $this->assertSame(25.0, $items[1]->fresh()->pos_y);
    }

    public function test_batch_positions_reject_items_from_other_games(): void
    {
        [$game] = $this->startedGame();
        [$other, $otherPlayers] = $this->startedGame();
        $foreign = GameItem::where(['game_id' => $other->id, 'player_id' => $otherPlayers[0]->id])->sole();

        $this->patch(route('gameitems.positions', $game->id), ['positions' => [
            ['id' => $foreign->id, 'x' => 10, 'y' => 10],
        ]])->assertStatus(422);

        $this->assertNull($foreign->fresh()->pos_x);
    }

    public function test_substitute_takes_outgoing_player_position(): void
    {
        [$game, $players1] = $this->startedGame();
        GameItem::where(['game_id' => $game->id, 'player_id' => $players1[0]->id])->update(['pos_x' => 20, 'pos_y' => 70]);
        $substitute = $this->makePlayers(Club::find($game->club1_id))->first();

        $this->post(route('gameitems.store'), [
            'player_id' => $substitute->id,
            'game_id' => $game->id,
            'entered_in' => 'cambio',
            'change_player_id' => $players1[0]->id,
        ])->assertOk();

        $item = GameItem::where(['game_id' => $game->id, 'player_id' => $substitute->id])->sole();
        $this->assertSame(20.0, $item->pos_x);
        $this->assertSame(70.0, $item->pos_y);
    }

    public function test_lineup_remembers_positions_from_previous_game(): void
    {
        [$previous, $players1, $players2] = $this->startedGame();
        GameItem::where(['game_id' => $previous->id, 'player_id' => $players1[0]->id])->update(['pos_x' => 5, 'pos_y' => 50]);

        $next = Game::factory()->create(['club1_id' => $previous->club1_id, 'club2_id' => $previous->club2_id, 'state' => 'planificado']);
        $newcomer = $this->makePlayers(Club::find($previous->club1_id))->first();

        $this->post(route('play.store', $next->id), [
            'clubs1' => [$players1[0]->id, $newcomer->id],
            'clubs2' => [$players2[0]->id],
        ])->assertRedirect(route('playing.index', $next->id));

        $veteran = GameItem::where(['game_id' => $next->id, 'player_id' => $players1[0]->id])->sole();
        $this->assertSame(5.0, $veteran->pos_x);
        $this->assertSame(50.0, $veteran->pos_y);

        // Sin partido anterior con posición: queda para ubicar a mano
        $this->assertNull(GameItem::where(['game_id' => $next->id, 'player_id' => $newcomer->id])->sole()->pos_x);
    }

    public function test_available_players_excludes_those_already_in_game(): void
    {
        [$game, $players1] = $this->startedGame();
        $bench = $this->makePlayers(Club::find($game->club1_id), 2);

        $response = $this->postJson(route('gameitems.players', $game->id), ['type' => 1])
            ->assertOk()
            ->assertJsonCount(2, 'result');

        $this->assertEqualsCanonicalizing(
            $bench->pluck('id')->all(),
            collect($response->json('result'))->pluck('id')->all()
        );
    }

    public function test_available_players_rejects_invalid_type(): void
    {
        [$game] = $this->startedGame();

        $this->postJson(route('gameitems.players', $game->id), ['type' => 'x'])
            ->assertUnprocessable();
    }

    public function test_game_can_be_finalized(): void
    {
        [$game] = $this->startedGame();

        $this->put(route('games.ended', $game->id), ['state' => 'finalizado'])
            ->assertRedirect(route('calendar'));

        $this->assertSame('finalizado', $game->fresh()->state);
    }

    public function test_deleting_game_removes_its_items(): void
    {
        [$game] = $this->startedGame();

        $this->delete(route('games.destroy', $game->id))->assertOk();

        $this->assertModelMissing($game);
        $this->assertSame(0, GameItem::count());
    }

    /**
     * Partido en estado "jugando" con 2 jugadores por club.
     */
    private function startedGame(): array
    {
        $club1 = $this->makeClub();
        $club2 = $this->makeClub();
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'state' => 'jugando']);
        $players1 = $this->makePlayers($club1, 2);
        $players2 = $this->makePlayers($club2, 2);

        foreach ($players1->concat($players2) as $player) {
            GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $player->id]);
        }

        return [$game, $players1, $players2];
    }
}
