<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameItem>
 */
class GameItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'player_id' => fn (array $attributes) => Player::factory()->create([
                'club_id' => Game::find($attributes['game_id'])->club1_id,
            ])->id,
            'entered_in' => 'inicio',
            'goals' => 0,
        ];
    }
}
