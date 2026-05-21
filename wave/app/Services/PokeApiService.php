<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PokeApiService
{
    public function resolvePokemon(string $id): array
    {
        $normalized = strtolower(trim($id));

        try {
            $response = Http::baseUrl('https://pokeapi.co/api/v2')
                ->timeout(8)
                ->get("/pokemon/{$normalized}");
        } catch (ConnectionException) {
            throw new HttpException(502, 'PokeAPI nie odpowiada.');
        }

        if ($response->status() === 404) {
            throw new NotFoundHttpException('Nie znaleziono pokemona.');
        }

        if ($response->failed()) {
            throw new HttpException(502, 'PokeAPI nie odpowiada.');
        }

        $data = $response->json();

        return [
            'pokemon_id' => $data['id'],
            'pokemon_name' => $data['name'],
        ];    
    }

    public function fetchPokemon(string $identifier): ?array
    {
        $normalized = strtolower(trim($identifier));

        try {
            $response = Http::baseUrl('https://pokeapi.co/api/v2')
                ->timeout(8)
                ->get("/pokemon/{$normalized}");
        } catch (ConnectionException) {
            throw new HttpException(502, 'PokeAPI nie odpowiada.');
        }

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            throw new HttpException(502, 'PokeAPI nie odpowiada.');
        }

        $data = $response->json();

        return [
            'id' => (int) $data['id'],
            'name' => (string) $data['name'],
            'height' => (int) $data['height'],
            'weight' => (int) $data['weight'],
            'base_experience' => (int) ($data['base_experience'] ?? 0),
            'types' => collect($data['types'] ?? [])
                ->map(fn (array $typeEntry) => $typeEntry['type']['name'] ?? null)
                ->filter()
                ->values()
                ->all(),
        ];
    }


}
