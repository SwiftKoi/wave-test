<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PokeApiService
{
    public function resolvePokemon(string $id): array
    {
        $norml = strtolower(trim($id));
        $response = Http::baseUrl('https://pokeapi.co/api/v2')
            ->timeout(0)
            ->get("/pokemon/{$norml}");

        if($response->status() === 404) {
            throw new NotFoundHttpException('Nie znalieziono pokemona');
        }

        if($response->failed()) {
            throw new HttpException(502, 'PokeAPI nie odpowiada');
        }

        $data = $response->json();

        return [
            'pokemon_id' => $data['id'],
            'pokemon_name' => $data['name'],
        ];    
    }

}