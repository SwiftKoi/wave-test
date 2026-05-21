<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchPokemonInfoRequest;
use App\Services\PokemonInfoService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;

#[Group('Pokemon Info')]
class PokemonInfoController extends Controller
{
    #[Header('X-SUPER-SECRET-KEY', '123')]
    #[BodyParam('pokemons', 'string[]', 'Lista nazw lub identyfikatorów pokemonów', required: true, example: ['pikachu', 'bulbasaur'])]
    #[Response([
        'data' => [
            [
                'id' => 1,
                'name' => 'bulbasaur',
                'height' => 7,
                'weight' => 69,
                'base_experience' => 64,
                'types' => ['grass', 'poison'],
                'source' => 'official',
            ],
        ],
        'meta' => [
            'requested' => 2,
            'allowed' => 1,
            'banned' => 1,
            'not_found' => 0,
        ],
    ], 200)]
    public function __invoke(FetchPokemonInfoRequest $request, PokemonInfoService $pokemonInfoService): JsonResponse
    {
        $result = $pokemonInfoService->fetchMany($request->input('pokemons', []));

        return response()->json($result);
    }
}
