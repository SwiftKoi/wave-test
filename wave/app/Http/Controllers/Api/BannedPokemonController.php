<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBannedPokemonRequest;
use App\Models\BannedPokemon;
use App\Services\PokeApiService;
use Illuminate\Http\JsonResponse;

class BannedPokemonController extends Controller
{
    public function index(): JsonResponse
    {
        $data = BannedPokemon::query()
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['data' => $data]);
    }


    public function store(StoreBannedPokemonRequest $request, PokeApiService $pokeApiService): JsonResponse
    {
        $resolved = $pokeApiService->resolvePokemon($request->string('pokemon')->toString());

        $exists = BannedPokemon::query()
            ->where('pokemon_id', $resolved['pokemon_id'])
            ->orWhere('pokemon_name', $resolved['pokemon_name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Pokemon został już zbanowany',
            ], 409);
        }

        $bannedPokemon = BannedPokemon::query()->create($resolved);

        return response()->json([
            'data' => $bannedPokemon,
        ], 201);
    }

    public function destroy(BannedPokemon $bannedPokemon): JsonResponse
    {
        $bannedPokemon->delete();

        return response()->json([], 204);
    }

}
