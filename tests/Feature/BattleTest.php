<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Battle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BattleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        Http::preventStrayRequests();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Monta um payload mínimo válido da PokéAPI.
     *
     * @return array<string, mixed>
     */
    private function fakePokemonPayload(string $name, int $hp): array
    {
        return [
            'name'    => $name,
            'sprites' => ['front_default' => "https://example.com/{$name}.png"],
            'types'   => [['type' => ['name' => 'normal']]],
            'stats'   => [
                ['base_stat' => $hp, 'stat' => ['name' => 'hp']],
                ['base_stat' => 60,  'stat' => ['name' => 'attack']],
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Casos de teste
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_index_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pokemon Battle');
    }

    /** @test */
    public function test_battle_shows_winner(): void
    {
        Http::fake([
            '*/pokemon/charizard' => Http::response($this->fakePokemonPayload('charizard', 78), 200),
            '*/pokemon/pikachu'   => Http::response($this->fakePokemonPayload('pikachu',   35), 200),
        ]);

        $response = $this->post('/battle', [
            'pokemon_one' => 'charizard',
            'pokemon_two' => 'pikachu',
        ]);

        $response->assertStatus(200);
        $response->assertSee('charizard');
        $response->assertSee('pikachu');
        $response->assertSee('78');
        $response->assertSee('35');
        $response->assertSee('venceu');
    }

    /** @test */
    public function test_battle_shows_draw(): void
    {
        Http::fake([
            '*/pokemon/eevee' => Http::response($this->fakePokemonPayload('eevee', 45), 200),
            '*/pokemon/ditto' => Http::response($this->fakePokemonPayload('ditto', 45), 200),
        ]);

        $response = $this->post('/battle', [
            'pokemon_one' => 'eevee',
            'pokemon_two' => 'ditto',
        ]);

        $response->assertStatus(200);
        $response->assertSee('45');

        // A view exibe "Empate!" no banner de resultado
        $response->assertSee('Empate');
    }

    /** @test */
    public function test_battle_shows_error_for_invalid_pokemon(): void
    {
        Http::fake([
            '*/pokemon/pokemon-invalido' => Http::response([], 404),
            '*/pokemon/pikachu'          => Http::response($this->fakePokemonPayload('pikachu', 35), 200),
        ]);

        $response = $this->post('/battle', [
            'pokemon_one' => 'pokemon-invalido',
            'pokemon_two' => 'pikachu',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['battle']);

        $errorMessage = session('errors')->first('battle');
        $this->assertStringContainsString('não encontrado', $errorMessage);
    }

    /** @test */
    public function test_battle_validates_required_fields(): void
    {
        $response = $this->post('/battle', [
            'pokemon_one' => '',
            'pokemon_two' => '',
        ]);

        $response->assertSessionHasErrors(['pokemon_one', 'pokemon_two']);
    }

    /** @test */
    public function test_battle_saves_to_database(): void
    {
        Http::fake([
            '*/pokemon/bulbasaur' => Http::response($this->fakePokemonPayload('bulbasaur', 45), 200),
            '*/pokemon/charmander' => Http::response($this->fakePokemonPayload('charmander', 39), 200),
        ]);

        $this->post('/battle', [
            'pokemon_one' => 'bulbasaur',
            'pokemon_two' => 'charmander',
        ]);

        $this->assertSame(1, Battle::count());

        $battle = Battle::first();
        $this->assertSame('bulbasaur',       $battle->pokemon_one_name);
        $this->assertSame('charmander',      $battle->pokemon_two_name);
        $this->assertSame('pokemon_one_wins', $battle->result);
    }

    /** @test */
    public function test_recent_battles_appear_on_index(): void
    {
        Battle::factory()->pokemonOneWins()->create([
            'pokemon_one_name' => 'snorlax',
            'pokemon_two_name' => 'magikarp',
        ]);

        Battle::factory()->pokemonTwoWins()->create([
            'pokemon_one_name' => 'rattata',
            'pokemon_two_name' => 'mewtwo',
        ]);

        Battle::factory()->draw()->create([
            'pokemon_one_name' => 'ditto',
            'pokemon_two_name' => 'ditto',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('snorlax');
        $response->assertSee('mewtwo');
        $response->assertSee('ditto');
    }
}
