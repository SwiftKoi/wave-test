<?php

namespace Tests\Feature;

use App\Models\BannedPokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PokemonInfoApiTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.super_secret_key', $this->secret);
    }

    public function test_info_requires_secret_header(): void
    {
        $this->postJson('/api/info', [
            'pokemons' => ['pikachu'],
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Brak naglowka autoryzacyjnego.');
    }

    public function test_info_validates_payload(): void
    {
        $this->postJson('/api/info', [], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Bledne dane wejsciowe.');
    }

    public function test_info_returns_only_allowed_pokemon(): void
    {
        BannedPokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/bulbasaur' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'height' => 7,
                'weight' => 69,
                'base_experience' => 64,
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
            ], 200),
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'types' => [
                    ['type' => ['name' => 'electric']],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/info', [
            'pokemons' => ['pikachu', 'bulbasaur'],
        ], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'bulbasaur')
            ->assertJsonPath('data.0.source', 'official')
            ->assertJsonPath('meta.requested', 2)
            ->assertJsonPath('meta.allowed', 1)
            ->assertJsonPath('meta.banned', 1);
    }

    public function test_info_counts_not_found_without_failing_request(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/missingno' => Http::response([], 404),
            'https://pokeapi.co/api/v2/pokemon/charmander' => Http::response([
                'id' => 4,
                'name' => 'charmander',
                'height' => 6,
                'weight' => 85,
                'base_experience' => 62,
                'types' => [
                    ['type' => ['name' => 'fire']],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/info', [
            'pokemons' => ['missingno', 'charmander'],
        ], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'charmander')
            ->assertJsonPath('meta.requested', 2)
            ->assertJsonPath('meta.not_found', 1);
    }

    public function test_info_returns_bad_gateway_when_pokeapi_is_unreachable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('blad polaczenia');
        });

        $response = $this->postJson('/api/info', [
            'pokemons' => ['pikachu'],
        ], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertStatus(502)
            ->assertExactJson([
                'message' => 'PokeAPI nie odpowiada.',
            ]);
    }
}
