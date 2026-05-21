<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannedPokemonRequest;
use App\Models\BannedPokemon;
use App\Services\BannedPokemonService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;

#[Group('Banned Pokemon')]
class BannedPokemonController extends Controller
{
    #[Header('X-SUPER-SECRET-KEY', '123')]
    #[Response(['data' => [['id' => 1, 'pokemon_id' => 25, 'pokemon_name' => 'pikachu']]], 200)]
    public function index(BannedPokemonService $bannedPokemonService): JsonResponse
    {
        $data = $bannedPokemonService->listLatest();
        return response()->json(['data' => $data]);
    }


    #[Header('X-SUPER-SECRET-KEY', '123')]
    #[BodyParam('pokemon', 'string', 'Nazwa lub identyfikator pokemona.', required: true, example: 'pikachu')]
    #[Response(['data' => ['id' => 1, 'pokemon_id' => 25, 'pokemon_name' => 'pikachu']], 201)]
    #[Response(['message' => 'Pokemon został już zbanowany.'], 409)]
    public function store(StoreBannedPokemonRequest $request, BannedPokemonService $bannedPokemonService): JsonResponse
    {
        $bannedPokemon = $bannedPokemonService->ban($request->string('pokemon')->toString());

        return response()->json([
            'data' => $bannedPokemon,
        ], 201);
    }

    #[Header('X-SUPER-SECRET-KEY', '123')]
    #[Response([], 204)]
    public function destroy(BannedPokemon $bannedPokemon, BannedPokemonService $bannedPokemonService): JsonResponse
    {
        $bannedPokemonService->unban($bannedPokemon);

        return response()->json([], 204);
    }

}
