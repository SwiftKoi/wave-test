<?php

namespace App\Services;

class PokemonInfoService
{
    public function __construct(
        private readonly PokeApiService $pokeApiService,
        private readonly BannedPokemonService $bannedPokemonService
    ) {
    }

    public function fetchMany(array $rawItems): array
    {
        $requested = collect($rawItems)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter()
            ->values();

        $bannedNames = $this->bannedPokemonService->getBannedNames($requested->all());

        $allowedInputs = $requested->reject(
            fn (string $item) => in_array($item, $bannedNames, true)
        );

        $data = [];
        $notFound = 0;

        foreach ($allowedInputs as $item) {
            $pokemon = $this->pokeApiService->fetchPokemon($item);

            if ($pokemon === null) {
                $notFound++;
                continue;
            }

            if ($this->bannedPokemonService->isBannedById($pokemon['id'])) {
                continue;
            }

            $pokemon['source'] = 'official';
            $data[] = $pokemon;
        }

        return [
            'data' => $data,
            'meta' => [
                'requested' => $requested->count(),
                'allowed' => count($data),
                'banned' => $requested->count() - $allowedInputs->count(),
                'not_found' => $notFound,
            ],
        ];
    }
}