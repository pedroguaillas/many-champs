<?php

namespace Tests\Feature\Domain;

use App\Models\Category;
use App\Models\Club;
use App\Models\Game;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Configuracion del campeonato: categorias, grupos, clubes, jugadores y pagos.
 */
class TournamentSetupTest extends DomainTestCase
{
    // Categorias

    public function test_categories_index_shows_only_current_championship(): void
    {
        $otherTeam = User::factory()->withPersonalTeam()->create()->ownedTeams()->first();
        Category::factory()->create(['team_id' => $otherTeam->id]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Category/Index')
                ->has('categories', 1)
                ->where('categories.0.id', $this->category->id)
            );
    }

    public function test_category_crud(): void
    {
        $this->post(route('categories.store'), [
            'name' => 'Sub 18',
            'inscription' => 120.5,
            'gender' => 'femenino',
        ])->assertOk();

        $category = Category::where('name', 'Sub 18')->sole();
        $this->assertSame($this->team->id, $category->team_id);
        $this->assertSame('120.50', $category->inscription);

        $this->put(route('categories.update', $category->id), [
            'name' => 'Sub 20',
            'inscription' => 100,
            'gender' => 'femenino',
        ])->assertOk();
        $this->assertSame('Sub 20', $category->fresh()->name);

        $this->delete(route('categories.destroy', $category->id))->assertOk();
        $this->assertModelMissing($category);
    }

    public function test_category_validation(): void
    {
        $this->post(route('categories.store'), ['name' => str_repeat('x', 21)])
            ->assertSessionHasErrors(['name', 'inscription', 'gender']);
    }

    // Grupos

    public function test_group_crud(): void
    {
        $this->post(route('groups.store'), ['name' => 'A', 'category_id' => $this->category->id])->assertOk();
        $group = Group::sole();

        $this->get(route('groups.index', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Group/Index')->has('groups', 1));

        $this->put(route('groups.update', $group->id), ['name' => 'B', 'category_id' => $this->category->id])->assertOk();
        // groups.name es char(255): pgsql lo devuelve con relleno de espacios
        $this->assertSame('B', trim($group->fresh()->name));

        $this->delete(route('groups.destroy', $group->id))->assertOk();
        $this->assertModelMissing($group);
    }

    public function test_group_validation(): void
    {
        $this->post(route('groups.store'), ['name' => 'A-1', 'category_id' => 999])
            ->assertSessionHasErrors(['name', 'category_id']);
    }

    // Clubes

    public function test_clubs_index_with_group_filter(): void
    {
        $groupA = $this->makeGroup('A');
        $groupB = $this->makeGroup('B');
        $this->makeClub(['group_id' => $groupA->id]);
        $clubB = $this->makeClub(['group_id' => $groupB->id]);

        $this->get(route('clubs.index', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Club/Index')
                ->has('clubs', 2)
                ->has('groups', 2)
            );

        $this->get(route('clubs.index', $this->category->id).'?search=B')
            ->assertInertia(fn (Assert $page) => $page
                ->has('clubs', 1)
                ->where('clubs.0.id', $clubB->id)
                ->where('clubs.0.gname', fn ($v) => trim($v) === 'B')
            );
    }

    public function test_clubs_index_flags_clubs_with_dependencies(): void
    {
        $club = $this->makeClub();
        $player = $this->makePlayers($club)->first();

        $this->get(route('clubs.index', $this->category->id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('clubs.0.player_id', $player->id)
                ->where('clubs.0.game_id', null)
                ->where('clubs.0.payment_id', null)
            );
    }

    public function test_club_crud(): void
    {
        $group = $this->makeGroup('A');

        $this->post(route('clubs.store'), [
            'name' => 'Leones',
            'address' => 'Av. Siempre Viva',
            'extra_points' => 2,
            'category_id' => $this->category->id,
            'group_id' => $group->id,
        ])->assertOk();

        $club = Club::sole();
        $this->assertSame($group->id, $club->group_id);
        $this->assertSame(2, $club->extra_points);

        $this->put(route('clubs.update', $club->id), [
            'name' => 'Leones FC',
            'address' => 'Centro',
            'extra_points' => 0,
            'group_id' => null,
        ])->assertOk();
        $this->assertSame('Leones FC', $club->fresh()->name);
        $this->assertNull($club->fresh()->group_id);

        $this->delete(route('clubs.destroy', $club->id))->assertOk();
        $this->assertModelMissing($club);
    }

    public function test_club_validation(): void
    {
        $this->post(route('clubs.store'), ['extra_points' => 50])
            ->assertSessionHasErrors(['name', 'address', 'extra_points']);
    }

    public function test_club_jersey_color_is_saved_and_validated(): void
    {
        $this->post(route('clubs.store'), [
            'name' => 'Rayados',
            'address' => 'Centro',
            'category_id' => $this->category->id,
            'color' => '#DC2626',
        ])->assertOk();

        $club = Club::where('name', 'Rayados')->sole();
        $this->assertSame('#DC2626', $club->color);

        $this->put(route('clubs.update', $club->id), ['name' => 'Rayados', 'address' => 'Centro', 'extra_points' => 0, 'color' => null])
            ->assertOk();
        $this->assertNull($club->fresh()->color);

        $this->post(route('clubs.store'), ['name' => 'X', 'address' => 'Y', 'color' => 'rojo'])
            ->assertSessionHasErrors('color');
    }

    public function test_playing_page_includes_club_colors(): void
    {
        $club1 = $this->makeClub(['color' => '#facc15']);
        $club2 = $this->makeClub(['color' => '#16a34a']);
        $game = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id, 'state' => 'jugando']);

        $this->get(route('playing.index', $game->id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('game.c1_color', '#facc15')
                ->where('game.c2_color', '#16a34a')
            );
    }

    public function test_club_with_games_cannot_be_deleted(): void
    {
        $club = $this->makeClub();
        Game::factory()->create(['club1_id' => $this->makeClub()->id, 'club2_id' => $club->id]);

        $this->delete(route('clubs.destroy', $club->id))
            ->assertStatus(422)
            ->assertJsonPath('msm', 'No se puede eliminar el club '.$club->name.' ya que esta registrado en un partido');

        $this->assertModelExists($club);
    }

    public function test_club_with_payments_cannot_be_deleted(): void
    {
        $club = $this->makeClub();
        Payment::factory()->create(['club_id' => $club->id]);

        $this->delete(route('clubs.destroy', $club->id))->assertStatus(422);
        $this->assertModelExists($club);
    }

    public function test_club_with_players_cannot_be_deleted(): void
    {
        $club = $this->makeClub();
        $this->makePlayers($club);

        $this->delete(route('clubs.destroy', $club->id))->assertStatus(422);
        $this->assertModelExists($club);
    }

    // Jugadores

    public function test_players_index(): void
    {
        $club = $this->makeClub();
        $this->makePlayers($club, 3);

        $this->get(route('players.index', $club->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/Index')
                ->where('club.id', $club->id)
                ->has('players', 3)
            );
    }

    public function test_player_can_be_created_with_photo(): void
    {
        Storage::fake('public');
        $club = $this->makeClub();

        $this->post(route('players.store'), [
            'cedula' => '0102030405',
            'first_name' => 'Lionel',
            'last_name' => 'Messi',
            'date_of_birth' => '1987-06-24',
            't_shirt' => 10,
            'phone' => '0999999999',
            'club_id' => $club->id,
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertOk();

        $player = Player::sole();
        $this->assertSame($this->team->id, $player->team_id);
        $this->assertSame('1987-06-24', $player->date_of_birth->toDateString());
        $this->assertNotNull($player->photo);
        $this->assertStringEndsWith('.jpg', $player->photo);
        Storage::disk('public')->assertExists('avatars/'.$player->photo);
    }

    public function test_player_photo_must_be_an_image(): void
    {
        $club = $this->makeClub();

        $this->post(route('players.store'), [
            'first_name' => 'Lionel',
            'last_name' => 'Messi',
            'club_id' => $club->id,
            'photo' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('photo');

        $this->assertSame(0, Player::count());
    }

    public function test_player_update_and_delete(): void
    {
        $player = $this->makePlayers($this->makeClub())->first();

        $this->put(route('players.update', $player->id), [
            'first_name' => 'Nuevo',
            'last_name' => 'Nombre',
            't_shirt' => 7,
        ])->assertOk();
        $this->assertSame('Nuevo', $player->fresh()->first_name);
        $this->assertSame(7, $player->fresh()->t_shirt);

        $this->delete(route('players.destroy', $player->id))->assertOk();
        $this->assertModelMissing($player);
    }

    // Pagos

    public function test_payment_crud(): void
    {
        $club = $this->makeClub();

        $this->post(route('payments.store'), [
            'club_id' => $club->id,
            'amount' => 35.75,
            'note' => 'Inscripcion',
        ])->assertOk();

        $payment = Payment::sole();
        $this->assertSame('35.75', $payment->amount);

        $this->get(route('payments.index', $club->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Payment/Index')
                ->has('payments', 1)
                ->where('payments.0.amount', '35.75')
            );

        $this->put(route('payments.update', $payment->id), ['amount' => 40])->assertOk();
        $this->assertSame('40.00', $payment->fresh()->amount);

        $this->delete(route('payments.destroy', $payment->id))->assertOk();
        $this->assertModelMissing($payment);
    }

    public function test_payment_validation(): void
    {
        $this->post(route('payments.store'), ['amount' => -5, 'club_id' => 999])
            ->assertSessionHasErrors(['amount', 'club_id']);
    }
}
