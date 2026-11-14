<?php

namespace Tests\Feature\Domain;

use App\Actions\Championship\PublishChampionship;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Páginas públicas de un campeonato compartido: solo lectura, sin sesión,
 * y sin datos personales.
 */
class PublicSiteTest extends DomainTestCase
{
    private function publish(?Team $team = null): Team
    {
        return app(PublishChampionship::class)->handle($team ?? $this->team);
    }

    private function asGuest(): void
    {
        // Tras una petición con sesión el guard activo puede ser el de sanctum: se reinician todos
        $this->app['auth']->forgetGuards();
        Auth::shouldUse('web');
        $this->flushSession();
        $this->assertGuest();
    }

    /** Partido en juego con un jugador por lado; el local lleva 2 goles y amarilla */
    private function liveGame(): array
    {
        $club1 = $this->makeClub(['color' => '#dc2626']);
        $club2 = $this->makeClub();
        $scorer = $this->makePlayers($club1, 1, ['cedula' => '1712345678', 'phone' => '0991234567', 'photo' => 'secreta.jpg'])[0];
        $rival = $this->makePlayers($club2, 1)[0];
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'state' => 'jugando']);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $scorer->id, 'goals' => 2, 'santion' => 'amarilla', 'card_black' => true, 'pos_x' => 30, 'pos_y' => 40]);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $rival->id, 'goals' => 1]);

        return [$game, $club1, $club2, $scorer];
    }

    public function test_championship_is_private_until_shared(): void
    {
        $this->assertFalse($this->team->is_public);
        $this->assertNull($this->team->slug);

        $this->post(route('championship.share'))->assertRedirect();

        $team = $this->team->fresh();
        $this->assertTrue($team->is_public);
        $this->assertNotEmpty($team->slug);

        // El panel recibe el enlace para mostrarlo en la hoja de compartir
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.current_team.slug', $team->slug)
            ->where('auth.user.current_team.is_public', true)
        );

        $this->asGuest();
        $this->get(route('public.championship', $team->slug))->assertOk();
    }

    public function test_unsharing_closes_the_pages_and_keeps_the_link_for_later(): void
    {
        $slug = $this->publish()->slug;

        $this->delete(route('championship.unshare'))->assertRedirect();
        $this->assertFalse($this->team->fresh()->is_public);

        $this->asGuest();
        $this->get(route('public.championship', $slug))->assertNotFound();
        $this->get(route('public.calendar', $slug))->assertNotFound();

        // Al volver a compartir, el enlace es el mismo
        $this->assertSame($slug, $this->publish($this->team->fresh())->slug);
    }

    public function test_only_the_owner_can_share(): void
    {
        $member = User::factory()->withPersonalTeam()->create();
        $this->team->users()->attach($member, ['role' => 'admin']);
        $member->switchTeam($this->team);

        $this->actingAs($member)->post(route('championship.share'))->assertForbidden();
        $this->assertFalse($this->team->fresh()->is_public);
    }

    public function test_slugs_are_unique_between_championships_with_the_same_name(): void
    {
        $this->team->update(['name' => 'Copa Barrial']);
        $other = User::factory()->withPersonalTeam()->create()->ownedTeams()->first();
        $other->update(['name' => 'Copa Barrial']);

        $this->assertSame('copa-barrial', $this->publish($this->team->fresh())->slug);
        $this->assertSame('copa-barrial-2', $this->publish($other->fresh())->slug);
    }

    public function test_championship_page_lists_categories_and_games(): void
    {
        [$game, $club1, $club2] = $this->liveGame();
        $slug = $this->publish()->slug;
        $this->asGuest();

        $this->get(route('public.championship', $slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Championship')
                ->where('championship.slug', $slug)
                ->has('categories', 1)
                ->where('categories.0.live', 1)
                ->has('live', 1)
                ->where('live.0.id', $game->id)
                ->where('live.0.c1name', $club1->name)
                ->where('live.0.score1', fn ($score) => (int) $score === 2)
                ->where('live.0.score2', fn ($score) => (int) $score === 1)
                ->has('og.title')
            );
    }

    public function test_link_preview_tags_are_in_the_html(): void
    {
        [$game, $club1, $club2] = $this->liveGame();
        $slug = $this->publish()->slug;
        $this->asGuest();

        $this->get(route('public.game', [$slug, $game->id]))
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee(e("{$club1->name} 2 - 1 {$club2->name}"), false);
    }

    public function test_game_page_shows_the_match_without_personal_data(): void
    {
        [$game, , , $scorer] = $this->liveGame();
        $slug = $this->publish()->slug;
        $this->asGuest();

        $response = $this->get(route('public.game', [$slug, $game->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Game')
                ->where('game.state', 'jugando')
                ->has('home', 1)
                ->where('home.0.first_name', $scorer->first_name)
                ->where('home.0.goals', 2)
                ->where('home.0.santion', 'amarilla')
                ->where('home.0.pos_x', fn ($x) => (float) $x === 30.0)
                ->missing('home.0.cedula')
                ->missing('home.0.phone')
                ->missing('home.0.photo')
                ->missing('home.0.date_of_birth')
                ->missing('home.0.card_black')
                ->missing('home.0.paid_santion')
                ->has('away', 1)
            );

        // Ni en props ni en ninguna otra parte de la respuesta
        $response->assertDontSee('1712345678')->assertDontSee('0991234567')->assertDontSee('secreta.jpg');
    }

    public function test_category_page_has_games_standings_and_scorers(): void
    {
        [$game, $club1, , $scorer] = $this->liveGame();
        $slug = $this->publish()->slug;
        $this->asGuest();

        $response = $this->get(route('public.category', [$slug, $this->category->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Category')
                ->has('games', 1)
                ->has('standings', 2)
                ->has('scorers', 2)
                ->where('scorers.0.first_name', $scorer->first_name)
                ->where('scorers.0.goals', 2)
                ->where('scorers.0.club', $club1->name)
            );

        $response->assertDontSee('1712345678')->assertDontSee('secreta.jpg');
    }

    public function test_calendar_shows_the_games_of_the_day(): void
    {
        $club1 = $this->makeClub();
        $club2 = $this->makeClub();
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'date' => '2026-12-05']);
        $slug = $this->publish()->slug;
        $this->asGuest();

        $this->get(route('public.calendar', [$slug, '2026-12-05']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Calendar')
                ->where('date', '2026-12-05')
                ->where('previous', '2026-12-04')
                ->where('next', '2026-12-06')
                ->has('games', 1)
                ->where('games.0.id', $game->id)
            );

        $this->get(route('public.calendar', [$slug, '2026-12-06']))
            ->assertInertia(fn (Assert $page) => $page->has('games', 0));

        // Una fecha mal escrita no rompe la página
        $this->get(route('public.calendar', [$slug, 'no-es-fecha']))->assertOk();
    }

    public function test_other_championships_data_is_not_reachable_through_a_public_one(): void
    {
        [$game] = $this->liveGame();
        $slug = $this->publish()->slug;

        // Otro campeonato, también público, no puede mostrar lo de este
        $other = User::factory()->withPersonalTeam()->create()->ownedTeams()->first();
        $otherSlug = $this->publish($other)->slug;
        $otherCategory = Category::factory()->create(['team_id' => $other->id]);
        $this->asGuest();

        $this->get(route('public.game', [$otherSlug, $game->id]))->assertNotFound();
        $this->get(route('public.category', [$otherSlug, $this->category->id]))->assertNotFound();
        $this->get(route('public.category', [$slug, $otherCategory->id]))->assertNotFound();
    }

    public function test_welcome_lists_only_shared_championships(): void
    {
        $this->team->update(['name' => 'Copa Visible']);
        $slug = $this->publish($this->team->fresh())->slug;
        User::factory()->withPersonalTeam()->create(); // campeonato privado
        $this->asGuest();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('championships', 1)
                ->where('championships.0.slug', $slug)
                ->where('championships.0.name', 'Copa Visible')
            );
    }
}
