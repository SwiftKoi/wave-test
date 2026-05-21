<?php

namespace Database\Factories;

use App\Models\BannedPokemon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BannedPokemon>
 */
class BannedPokemonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = BannedPokemon::class;

    public function definition(): array
    {
        $pokemonId = fake()->unique()->numberBetween(1, 10000);

        return [
            'pokemon_id' => $pokemonId,
            'pokemon_name' => 'pokemon-' . $pokemonId,
        ];
    }
}
