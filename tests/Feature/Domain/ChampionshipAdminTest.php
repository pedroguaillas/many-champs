<?php

namespace Tests\Feature\Domain;

use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Administracion de campeonatos (solo superadmin).
 */
class ChampionshipAdminTest extends DomainTestCase
{
    public function test_regular_users_cannot_access_admin(): void
    {
        $this->get(route('admin.championships.index'))->assertForbidden();
        $this->post(route('admin.championships.store'), [])->assertForbidden();
    }

    public function test_superadmin_flag_is_shared_with_frontend(): void
    {
        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('is_superadmin', false));

        $this->actingAs($this->superadmin())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('is_superadmin', true));
    }

    public function test_superadmin_lists_non_personal_championships(): void
    {
        $admin = $this->superadmin();
        $admin->ownedTeams()->create(['name' => 'Copa Barrial', 'personal_team' => false]);

        $this->actingAs($admin)
            ->get(route('admin.championships.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Championships')
                ->has('championships', 1)
                ->where('championships.0.name', 'Copa Barrial')
                ->where('championships.0.owner.email', $admin->email)
            );
    }

    public function test_superadmin_creates_championship_for_existing_user(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.championships.store'), [
                'name' => 'Liga Norte',
                'owner_mode' => 'existing',
                'owner_id' => $this->user->id,
            ])->assertRedirect(route('admin.championships.index'));

        $team = Team::where('name', 'Liga Norte')->sole();
        $this->assertSame($this->user->id, $team->user_id);
        $this->assertFalse($team->personal_team);
        $this->assertSame($team->id, $this->user->fresh()->current_team_id);
    }

    public function test_superadmin_creates_championship_with_new_owner(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.championships.store'), [
                'name' => 'Liga Sur',
                'owner_mode' => 'new',
                'owner_name' => 'Organizador',
                'owner_email' => 'org@example.com',
                'owner_password' => 'secret-password',
            ])->assertRedirect(route('admin.championships.index'));

        $owner = User::where('email', 'org@example.com')->sole();
        $this->assertCount(2, $owner->ownedTeams);
        $this->assertTrue($owner->ownedTeams->contains('personal_team', true));
        $this->assertSame('Liga Sur', $owner->currentTeam->name);
    }

    public function test_store_validates_new_owner(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.championships.store'), [
                'name' => 'Liga',
                'owner_mode' => 'new',
                'owner_email' => $this->user->email,
                'owner_password' => 'short',
            ])->assertSessionHasErrors(['owner_name', 'owner_email', 'owner_password']);
    }

    public function test_search_user_by_email(): void
    {
        $this->actingAs($this->superadmin())
            ->postJson(route('admin.championships.search-user'), ['email' => $this->user->email])
            ->assertOk()
            ->assertJsonPath('user.id', $this->user->id)
            ->assertJsonMissingPath('user.password');
    }

    public function test_superadmin_renames_and_deletes_championship(): void
    {
        $admin = $this->superadmin();
        $team = $admin->ownedTeams()->create(['name' => 'Viejo', 'personal_team' => false]);

        $this->actingAs($admin)
            ->put(route('admin.championships.update', $team->id), ['name' => 'Nuevo'])
            ->assertRedirect();
        $this->assertSame('Nuevo', $team->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.championships.destroy', $team->id))
            ->assertRedirect(route('admin.championships.index'));
        $this->assertModelMissing($team);
    }

    private function superadmin(): User
    {
        return User::factory()->withPersonalTeam()->create(['is_superadmin' => true]);
    }
}
