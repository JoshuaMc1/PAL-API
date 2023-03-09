<?php

namespace Database\Factories;

use App\Models\AniList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AniList>
 */
class AniListFactory extends Factory
{
    protected $model = AniList::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'overall_rating' => fake()->numberBetween(0, 5),
            'animation_rating' => fake()->numberBetween(0, 5),
            'history_rating' => fake()->numberBetween(0, 5),
            'characters_rating' => fake()->numberBetween(0, 5),
            'music_rating' => fake()->numberBetween(0, 5),
            'the_good' => fake()->text(),
            'the_bad' => fake()->text(),
            'user_id' => User::all()->random()->id,
            'anime_id' => fake()->numberBetween(1, 15000)
        ];
    }
}
