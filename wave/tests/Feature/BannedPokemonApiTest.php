<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Tests\TestCase;
use App\Models\BannedPokemon;
use Illuminate\Support\Facades\Http;

class BannedPokemonApiTest extends TestCase
{
    use RefreshDatabase;


    private string $secret = '123';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.super_secret_key', $this->secret);
    }


    public function test_banned_routes_require_secret_header(): void
    {
        $this->getJson('/api/banned')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Brak naglowka autoryzacyjnego.');
    }

    public function test_banned_routes_reject_invalid_secret_header(): void
    {
        $this->getJson('/api/banned', [
            'X-SUPER-SECRET-KEY' => 'bad-secret',
        ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Niepoprawny klucz autoryzacyjny.');
    }

    public function test_it_lists_banned_pokemon(): void
    {
        BannedPokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);

        $response = $this->getJson('/api/banned', [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.pokemon_name', 'pikachu');
    }

    public function test_it_adds_banned_pokemon(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
            ], 200),
        ]);

        $response = $this->postJson(
            '/api/banned',
            [
                'pokemon' => 'pikachu',
            ],
            [
                'X-SUPER-SECRET-KEY' => $this->secret,
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.pokemon_id', 25)
            ->assertJsonPath('data.pokemon_name', 'pikachu');

        $this->assertDatabaseHas('banned_pokemons', [
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);
    }

    public function test_it_returns_conflict_when_pokemon_is_already_banned(): void
    {
        BannedPokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
            ], 200),
        ]);

        $response = $this->postJson(
            '/api/banned',
            [
                'pokemon' => 'pikachu',
            ],
            [
                'X-SUPER-SECRET-KEY' => $this->secret,
            ]
        );

        $response->assertStatus(409);
    }

    public function test_it_returns_not_found_when_pokeapi_does_not_know_pokemon(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/missingno' => Http::response([], 404),
        ]);

        $response = $this->postJson(
            '/api/banned',
            [
                'pokemon' => 'missingno',
            ],
            [
                'X-SUPER-SECRET-KEY' => $this->secret,
            ]
        );

        $response->assertNotFound();
    }

    public function test_it_deletes_banned_pokemon(): void
    {
        $bannedPokemon = BannedPokemon::factory()->create();

        $response = $this->deleteJson('/api/banned/' . $bannedPokemon->id, [], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('banned_pokemons', [
            'id' => $bannedPokemon->id,
        ]);
    }

    public function test_it_validates_required_pokemon_field(): void
    {
        $response = $this->postJson('/api/banned', [], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Bledne dane wejsciowe.')
            ->assertJsonValidationErrors(['pokemon']);
    }

    public function test_it_returns_clean_not_found_when_deleting_missing_resource(): void
    {
        $response = $this->deleteJson('/api/banned/1212', [], [
            'X-SUPER-SECRET-KEY' => $this->secret,
        ]);

        $response
            ->assertStatus(404)
            ->assertExactJson([
                'message' => 'Nie znaleziono zasobu.',
            ]);
    }

    public function test_it_returns_bad_gateway_when_pokeapi_is_unreachable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('blad polaczenia');
        });

        $response = $this->postJson('/api/banned', [
            'pokemon' => 'pikachu',
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
