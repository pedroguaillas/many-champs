<?php

namespace Tests\Feature\Domain;

use App\Models\Category;
use App\Models\Club;
use App\Models\Group;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\ProgressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base para los tests del dominio (campeonatos, partidos, tablas).
 *
 * Crea un usuario logeado cuyo team actual es el campeonato,
 * y siembra las fases (progress) que usan los partidos.
 */
abstract class DomainTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Team $team;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Dentro de la transaccion del test: RefreshDatabase solo siembra
        // en la primera migracion del proceso (puede ser un test de Jetstream)
        $this->seed(ProgressSeeder::class);

        $this->user = User::factory()->withPersonalTeam()->create();
        $this->team = $this->user->ownedTeams()->first();
        $this->user->switchTeam($this->team);

        $this->category = Category::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user);
    }

    protected function makeGroup(string $name = 'A', ?Category $category = null): Group
    {
        return Group::factory()->create([
            'name' => $name,
            'category_id' => ($category ?? $this->category)->id,
        ]);
    }

    protected function makeClub(array $attributes = [], ?Category $category = null): Club
    {
        return Club::factory()->create(array_merge([
            'category_id' => ($category ?? $this->category)->id,
        ], $attributes));
    }

    protected function makePlayers(Club $club, int $count = 1, array $attributes = [])
    {
        return Player::factory()->count($count)->create(array_merge([
            'club_id' => $club->id,
        ], $attributes));
    }
}
