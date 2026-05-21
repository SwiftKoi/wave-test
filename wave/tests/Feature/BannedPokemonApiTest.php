<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\BannedPokemon;
use Illuminate\Support\Facades\Http;

class BannedPokemonApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_banned_pokemon(): void
    {
        BannedPokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);

        $response = $this->getJson('/api/banned');

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

        $response = $this->postJson('/api/banned', [
            'pokemon' => 'pikachu',
        ]);

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

        $response = $this->postJson('/api/banned', [
            'pokemon' => 'pikachu',
        ]);

        $response->assertStatus(409);
    }

    public function test_it_returns_not_found_when_pokeapi_does_not_know_pokemon(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/missingno' => Http::response([], 404),
        ]);

        $response = $this->postJson('/api/banned', [
            'pokemon' => 'missingno',
        ]);

        $response->assertNotFound();
    }

    public function test_it_deletes_banned_pokemon(): void
    {
        $bannedPokemon = BannedPokemon::factory()->create();

        $response = $this->deleteJson('/api/banned/' . $bannedPokemon->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('banned_pokemons', [
            'id' => $bannedPokemon->id,
        ]);
    }

    public function test_it_validates_required_pokemon_field(): void
    {
        $response = $this->postJson('/api/banned', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pokemon']);
    }
}
