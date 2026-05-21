<?php

namespace App\Services;

use App\Exceptions\PokemonAlreadyBannedException;
use App\Models\BannedPokemon;
use Illuminate\Database\Eloquent\Collection;

class BannedPokemonService
{
    public function __construct(
        private readonly PokeApiService $pokeApiService
    ) {
    }

    public function listLatest(): Collection
    {
        return BannedPokemon::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function ban(string $pokemonIdentifier): BannedPokemon
    {
        $resolved = $this->pokeApiService->resolvePokemon($pokemonIdentifier);

        $exists = BannedPokemon::query()
            ->where('pokemon_id', $resolved['pokemon_id'])
            ->orWhere('pokemon_name', $resolved['pokemon_name'])
            ->exists();

        if ($exists) {
            throw new PokemonAlreadyBannedException('Pokemon zostal juz zbanowany.');
        }

        return BannedPokemon::query()->create($resolved);
    }

    public function unban(BannedPokemon $bannedPokemon): void
    {
        $bannedPokemon->delete();
    }

    public function getBannedNames(array $normalizedInputs): array
    {
        return BannedPokemon::query()
            ->whereIn('pokemon_name', $normalizedInputs)
            ->pluck('pokemon_name')
            ->map(fn (string $name) => strtolower($name))
            ->all();
    }

    public function isBannedById(int $pokemonId): bool
    {
        return BannedPokemon::query()
            ->where('pokemon_id', $pokemonId)
            ->exists();
    }
}
