<?php

namespace Tests\Feature\Domain;

use App\Models\Game;
use App\Models\GameItem;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Dashboard, seleccion de categoria, calendario y diario.
 */
class HomeTest extends DomainTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        auth()->logout();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('games.index', $this->category->id))->assertRedirect(route('login'));
    }

    public function test_layout_is_installable_and_mobile_ready(): void
    {
        $html = $this->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="manifest" href="/manifest.webmanifest">', $html);
        $this->assertStringContainsString('<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">', $html);
        $this->assertStringContainsString('viewport-fit=cover', $html);
        // Nunca bloquear el zoom (accesibilidad)
        $this->assertStringNotContainsString('user-scalable=no', $html);
        $this->assertStringNotContainsString('maximum-scale=1', $html);

        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $manifest['display']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
    }

    public function test_dashboard_counts(): void
    {
        $club1 = $this->makeClub();
        $club2 = $this->makeClub();
        $players = $this->makePlayers($club1, 3);
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        GameItem::factory()->create(['game_id' => $game->id, 'player_id' => $players[0]->id, 'santion' => 'amarilla']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('players', 3)
                ->where('clubs', 2)
                ->where('games', 1)
                ->where('sanctions', 1)
            );
    }

    public function test_select_category_lists_categories_with_club_count(): void
    {
        $this->makeClub();
        $this->makeClub();

        $this->get(route('select.category', 'partidos'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SelectCategory')
                ->where('type', 'games')
                ->has('categories', 1)
                ->where('categories.0.count', 2)
            );
    }

    public function test_select_category_rejects_unknown_type(): void
    {
        $this->get(route('select.category', 'hack'))->assertNotFound();
    }

    public function test_calendar_shows_games_of_date(): void
    {
        $group = $this->makeGroup('A');
        $club1 = $this->makeClub(['group_id' => $group->id, 'name' => 'Leones']);
        $club2 = $this->makeClub(['group_id' => $group->id, 'name' => 'Tigres']);
        Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'date' => '2026-08-01', 'time' => '18:00']);
        Game::factory()->create(['club1_id' => $club2->id, 'club2_id' => $club1->id, 'date' => '2026-08-01', 'time' => '09:00']);
        Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'date' => '2026-08-02']);

        $this->get(route('calendar', '2026-08-01'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Calendar')
                ->where('date', '2026-08-01')
                ->has('games', 2)
                ->where('games.0.c1name', 'Tigres') // ordenado por hora
                ->where('games.1.c1name', 'Leones')
                // groups.name es char(255): pgsql lo devuelve con relleno de espacios
                ->where('games.1.g_name', fn ($v) => trim($v) === 'A')
                ->where('games.1.name', $this->category->name)
            );
    }

    public function test_calendar_defaults_to_today_and_ignores_invalid_date(): void
    {
        Carbon::setTestNow('2026-09-15 10:00:00');
        Game::factory()->create(['club1_id' => $this->makeClub()->id, 'date' => '2026-09-15']);

        $this->get(route('calendar'))
            ->assertInertia(fn (Assert $page) => $page->where('date', '2026-09-15')->has('games', 1));

        $this->get(route('calendar', 'not-a-date'))
            ->assertInertia(fn (Assert $page) => $page->where('date', '2026-09-15'));
    }

    public function test_diary_summarizes_todays_payments_and_paid_cards(): void
    {
        Carbon::setTestNow('2026-09-15 10:00:00');
        $club = $this->makeClub();
        Payment::factory()->create(['club_id' => $club->id, 'amount' => 20]);
        Payment::factory()->create(['club_id' => $club->id, 'created_at' => '2026-09-14 10:00:00']);

        $game = Game::factory()->create(['club1_id' => $club->id]);
        GameItem::factory()->create(['game_id' => $game->id, 'santion' => 'roja', 'paid_santion' => '2026-09-15']);
        GameItem::factory()->create(['game_id' => $game->id, 'santion' => 'amarilla', 'paid_santion' => '2026-09-15']);
        GameItem::factory()->create(['game_id' => $game->id, 'santion' => 'amarilla', 'paid_santion' => '2026-09-01']);
        GameItem::factory()->create(['game_id' => $game->id, 'card_black' => true, 'paid_black' => '2026-09-15']);

        $this->get(route('diary'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Diary')
                ->has('payments', 1)
                ->where('cardred', 1)
                ->where('cardyellow', 1)
                ->where('cardblack', 1)
            );
    }
}
