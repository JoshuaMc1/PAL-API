<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AniList;
use App\Models\FavoriteCharacter;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavoriteCharacter>
 */
class FavoriteCharacterFactory extends Factory
{
    protected $model = FavoriteCharacter::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => fake()->randomDigit(),
            'list_id' => AniList::all()->random()->id
        ];
    }
}
