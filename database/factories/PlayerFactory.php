<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            // El jugador pertenece al mismo campeonato (team) que la categoria del club
            'team_id' => fn (array $attributes) => Category::find(Club::find($attributes['club_id'])->category_id)->team_id,
            'cedula' => $this->faker->numerify('##########'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'date_of_birth' => $this->faker->date(),
            't_shirt' => $this->faker->numberBetween(1, 99),
            'phone' => $this->faker->numerify('09########'),
        ];
    }
}
