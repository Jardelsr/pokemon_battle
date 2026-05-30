<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\BattleResultDTO;
use App\DTOs\PokemonDTO;
use App\Exceptions\PokemonNotFoundException;
use App\Exceptions\PokemonValidationException;
use App\Models\Battle;
use App\Services\BattleService;
use App\Services\PokeApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class BattleServiceTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $pokeApiMock;
    private BattleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pokeApiMock = Mockery::mock(PokeApiService::class);
        $this->service     = new BattleService($this->pokeApiMock, new Battle());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Cria um PokemonDTO mínimo para uso nos testes.
     */
    private function makePokemonDTO(string $name, int $hp): PokemonDTO
    {
        return new PokemonDTO(
            name:      $name,
            hp:        $hp,
            spriteUrl: "https://example.com/{$name}.png",
            types:     ['normal'],
            stats:     ['hp' => $hp],
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Casos de teste
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_pokemon_one_wins_when_has_higher_hp(): void
    {
        $charizard = $this->makePokemonDTO('charizard', 78);
        $pikachu   = $this->makePokemonDTO('pikachu', 35);

        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('charizard')->once()->andReturn($charizard);
        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('pikachu')->once()->andReturn($pikachu);

        $result = $this->service->battle('charizard', 'pikachu');

        $this->assertInstanceOf(BattleResultDTO::class, $result);
        $this->assertSame('pokemon_one_wins', $result->result);
        $this->assertSame('charizard', $result->winnerName);
        $this->assertFalse($result->isDraw());
        $this->assertSame('charizard', $result->getWinner()->name);
    }

    /** @test */
    public function test_pokemon_two_wins_when_has_higher_hp(): void
    {
        $charizard = $this->makePokemonDTO('charizard', 78);
        $mewtwo    = $this->makePokemonDTO('mewtwo', 106);

        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('charizard')->once()->andReturn($charizard);
        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('mewtwo')->once()->andReturn($mewtwo);

        $result = $this->service->battle('charizard', 'mewtwo');

        $this->assertSame('pokemon_two_wins', $result->result);
        $this->assertSame('mewtwo', $result->winnerName);
        $this->assertSame('mewtwo', $result->getWinner()->name);
    }

    /** @test */
    public function test_draw_when_both_have_equal_hp(): void
    {
        $eevee = $this->makePokemonDTO('eevee', 45);
        $ditto = $this->makePokemonDTO('ditto', 45);

        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('eevee')->once()->andReturn($eevee);
        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('ditto')->once()->andReturn($ditto);

        $result = $this->service->battle('eevee', 'ditto');

        $this->assertSame('draw', $result->result);
        $this->assertNull($result->winnerName);
        $this->assertTrue($result->isDraw());
        $this->assertNull($result->getWinner());
    }

    /** @test */
    public function test_battle_throws_validation_exception_when_first_pokemon_not_found(): void
    {
        $this->pokeApiMock
            ->shouldReceive('getPokemon')
            ->with('fakemon')
            ->once()
            ->andThrow(new PokemonNotFoundException('fakemon'));

        $this->pokeApiMock
            ->shouldReceive('getPokemon')
            ->with('pikachu')
            ->once()
            ->andReturn($this->makePokemonDTO('pikachu', 35));

        $this->expectException(PokemonValidationException::class);
        $this->expectExceptionMessage("Pokémon 'fakemon' não encontrado.");

        $this->service->battle('fakemon', 'pikachu');
    }

    /** @test */
    public function test_battle_collects_both_errors_when_both_pokemons_not_found(): void
    {
        $this->pokeApiMock
            ->shouldReceive('getPokemon')
            ->with('fakemon1')
            ->once()
            ->andThrow(new PokemonNotFoundException('fakemon1'));

        $this->pokeApiMock
            ->shouldReceive('getPokemon')
            ->with('fakemon2')
            ->once()
            ->andThrow(new PokemonNotFoundException('fakemon2'));

        $this->expectException(PokemonValidationException::class);

        try {
            $this->service->battle('fakemon1', 'fakemon2');
        } catch (PokemonValidationException $e) {
            $messages = $e->getMessages();
            $this->assertCount(2, $messages);
            $this->assertStringContainsString('fakemon1', $messages[0]);
            $this->assertStringContainsString('fakemon2', $messages[1]);
            throw $e;
        }
    }

    /** @test */
    public function test_battle_persists_result_in_database(): void
    {
        $charizard = $this->makePokemonDTO('charizard', 78);
        $pikachu   = $this->makePokemonDTO('pikachu', 35);

        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('charizard')->once()->andReturn($charizard);
        $this->pokeApiMock
            ->shouldReceive('getPokemon')->with('pikachu')->once()->andReturn($pikachu);

        $this->service->battle('charizard', 'pikachu');

        $this->assertSame(1, Battle::count());

        $battle = Battle::first();
        $this->assertSame('charizard',         $battle->pokemon_one_name);
        $this->assertSame(78,                  $battle->pokemon_one_hp);
        $this->assertSame('pikachu',           $battle->pokemon_two_name);
        $this->assertSame(35,                  $battle->pokemon_two_hp);
        $this->assertSame('pokemon_one_wins',  $battle->result);
        $this->assertSame('charizard',         $battle->winner_name);
    }

    /** @test */
    public function test_get_recent_battles_returns_limited_results(): void
    {
        $now = now();

        // Cria 7 batalhas com timestamps explícitos e crescentes
        // para garantir ordenação determinística.
        // Nota: created_at não está em $fillable, então DB::table() é
        // usado para o update direto, contornando o Eloquent.
        for ($i = 1; $i <= 7; $i++) {
            $battle = Battle::create([
                'pokemon_one_name' => "pokemon{$i}",
                'pokemon_one_hp'   => 50,
                'pokemon_two_name' => "foe{$i}",
                'pokemon_two_hp'   => 45,
                'winner_name'      => "pokemon{$i}",
                'result'           => 'pokemon_one_wins',
            ]);

            DB::table('battles')
                ->where('id', $battle->id)
                ->update(['created_at' => $now->copy()->addSeconds($i)]);
        }

        $result = $this->service->getRecentBattles(5);

        // Apenas os 5 mais recentes (pokemon7 → pokemon3)
        $this->assertCount(5, $result);

        // Ordenação DESC: pokemon7 (now+7s) é o mais recente
        $this->assertSame('pokemon7', $result->first()->pokemon_one_name);

        // pokemon3 (now+3s) é o 5º mais recente; pokemon1 e pokemon2 ficam fora
        $this->assertSame('pokemon3', $result->last()->pokemon_one_name);
    }
}
