<?php

namespace Tests\Feature\Domain;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameItem;
use App\Models\Progress;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Generacion automatica de partidos (todos contra todos, solo ida) por grupo.
 */
class GenerateGamesTest extends DomainTestCase
{
    public function test_generate_page_renders(): void
    {
        $this->get(route('games.vgenerate', $this->category->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game/GenerateGames')
                ->where('category.id', $this->category->id)
            );
    }

    public function test_round_robin_with_even_number_of_clubs(): void
    {
        $group = $this->makeGroup('A');
        $clubs = collect(range(1, 4))->map(fn () => $this->makeClub(['group_id' => $group->id]));

        $this->post(route('games.sgenerate', $this->category->id), [
            'date' => '2026-07-04',
            'time' => '08:30',
        ])->assertRedirect(route('games.index', $this->category->id));

        $this->assertSame(6, Game::count());
        $this->assertEveryPairPlaysOnce($clubs->pluck('id')->all());

        $game = Game::first();
        $this->assertSame('2026-07-04', $game->date->toDateString());
        $this->assertSame('08:30', $game->time->format('H:i'));
        $this->assertSame('creado', $game->state);
        $this->assertSame(Progress::first()->id, $game->progress_id);
    }

    public function test_round_robin_with_odd_number_of_clubs_skips_bye(): void
    {
        $group = $this->makeGroup('A');
        $clubs = collect(range(1, 5))->map(fn () => $this->makeClub(['group_id' => $group->id]));

        $this->post(route('games.sgenerate', $this->category->id), [
            'date' => '2026-07-04',
            'time' => '08:30',
        ]);

        // 5 clubes => C(5,2) = 10 partidos, sin partidos contra el "descanso" (id 0)
        $this->assertSame(10, Game::count());
        $this->assertSame(0, Game::where('club1_id', 0)->orWhere('club2_id', 0)->count());
        $this->assertEveryPairPlaysOnce($clubs->pluck('id')->all());
    }

    public function test_games_are_generated_per_group_only(): void
    {
        $groupA = $this->makeGroup('A');
        $groupB = $this->makeGroup('B');
        $a = collect(range(1, 3))->map(fn () => $this->makeClub(['group_id' => $groupA->id]));
        $b = collect(range(1, 4))->map(fn () => $this->makeClub(['group_id' => $groupB->id]));
        // Club sin grupo no participa
        $this->makeClub(['group_id' => null]);

        $this->post(route('games.sgenerate', $this->category->id), [
            'date' => '2026-07-04',
            'time' => '08:30',
        ]);

        $this->assertSame(3 + 6, Game::count());

        // Ningun cruce entre grupos
        foreach (Game::all() as $game) {
            $sameGroup = ($a->contains('id', $game->club1_id) && $a->contains('id', $game->club2_id))
                || ($b->contains('id', $game->club1_id) && $b->contains('id', $game->club2_id));
            $this->assertTrue($sameGroup, "Partido {$game->club1_id} vs {$game->club2_id} cruza grupos");
        }
    }

    public function test_regenerating_replaces_existing_games_of_category_only(): void
    {
        $group = $this->makeGroup('A');
        $club1 = $this->makeClub(['group_id' => $group->id]);
        $club2 = $this->makeClub(['group_id' => $group->id]);

        $old = Game::factory()->create(['club1_id' => $club1->id, 'club2_id' => $club2->id]);
        GameItem::factory()->create(['game_id' => $old->id]);

        // Partido de otra categoria del mismo campeonato
        $other = Category::factory()->create(['team_id' => $this->team->id]);
        $foreign = Game::factory()->create(['club1_id' => $this->makeClub([], $other)->id]);

        $this->post(route('games.sgenerate', $this->category->id), [
            'date' => '2026-07-04',
            'time' => '08:30',
        ]);

        $this->assertModelMissing($old);
        $this->assertSame(0, GameItem::where('game_id', $old->id)->count());
        $this->assertModelExists($foreign);
        $this->assertSame(2, Game::count());
    }

    public function test_generate_validates_date_and_time(): void
    {
        $this->post(route('games.sgenerate', $this->category->id), [
            'date' => 'no-date',
            'time' => '8.30',
        ])->assertSessionHasErrors(['date', 'time']);
    }

    private function assertEveryPairPlaysOnce(array $clubIds): void
    {
        $pairs = Game::all()->map(function ($game) {
            $pair = [$game->club1_id, $game->club2_id];
            sort($pair);

            return implode('-', $pair);
        });

        $this->assertSame($pairs->count(), $pairs->unique()->count(), 'Hay partidos repetidos');

        $expected = count($clubIds) * (count($clubIds) - 1) / 2;
        $this->assertSame($expected, $pairs->count());

        foreach ($clubIds as $i => $a) {
            foreach (array_slice($clubIds, $i + 1) as $b) {
                $pair = [$a, $b];
                sort($pair);
                $this->assertContains(implode('-', $pair), $pairs->all());
            }
        }
    }
}
