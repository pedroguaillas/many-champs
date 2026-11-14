<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club1_id' => Club::factory(),
            'club2_id' => fn (array $attributes) => Club::factory()->create([
                'category_id' => Club::find($attributes['club1_id'])->category_id,
            ])->id,
            // Requiere ProgressSeeder
            'progress_id' => fn () => Progress::query()->value('id'),
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'state' => 'creado',
        ];
    }
}
