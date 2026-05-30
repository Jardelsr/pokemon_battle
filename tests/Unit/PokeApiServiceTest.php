<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\PokemonDTO;
use App\Exceptions\PokeApiException;
use App\Exceptions\PokemonNotFoundException;
use App\Services\PokeApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PokeApiServiceTest extends TestCase
{
    private PokeApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->service = app(PokeApiService::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Fixtures
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Payload mínimo válido da PokéAPI para o Pikachu.
     *
     * @return array<string, mixed>
     */
    private function fakePikachuResponse(): array
    {
        return [
            'name'    => 'pikachu',
            'stats'   => [
                ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                ['base_stat' => 55, 'stat' => ['name' => 'attack']],
            ],
            'sprites' => ['front_default' => 'https://example.com/pikachu.png'],
            'types'   => [['type' => ['name' => 'electric']]],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Casos de teste
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_returns_pokemon_dto_for_valid_pokemon(): void
    {
        Http::fake([
            '*/pokemon/pikachu' => Http::response($this->fakePikachuResponse(), 200),
        ]);

        $dto = $this->service->getPokemon('pikachu');

        $this->assertInstanceOf(PokemonDTO::class, $dto);
        $this->assertSame('pikachu', $dto->name);
        $this->assertSame(35, $dto->hp);
        $this->assertSame(['electric'], $dto->types);
        $this->assertSame('https://example.com/pikachu.png', $dto->spriteUrl);
        $this->assertSame(['hp' => 35, 'attack' => 55], $dto->stats);
    }

    /** @test */
    public function test_throws_pokemon_not_found_for_404(): void
    {
        Http::fake([
            '*/pokemon/invalid-pokemon' => Http::response([], 404),
        ]);

        try {
            $this->service->getPokemon('invalid-pokemon');
            $this->fail('PokemonNotFoundException não foi lançada.');
        } catch (PokemonNotFoundException $e) {
            $this->assertSame('invalid-pokemon', $e->pokemonName);
            $this->assertSame(
                "Pokémon 'invalid-pokemon' não encontrado.",
                $e->getMessage(),
            );
        }
    }

    /** @test */
    public function test_throws_pokeapi_exception_for_500(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $this->expectException(PokeApiException::class);

        $this->service->getPokemon('pikachu');
    }

    /** @test */
    public function test_throws_pokeapi_exception_for_connection_failure(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->expectException(PokeApiException::class);
        $this->expectExceptionMessage('O serviço Pokémon está temporariamente indisponível.');

        $this->service->getPokemon('pikachu');
    }

    /** @test */
    public function test_normalizes_pokemon_name_before_api_call(): void
    {
        Http::fake([
            '*/pokemon/pikachu' => Http::response($this->fakePikachuResponse(), 200),
        ]);

        $this->service->getPokemon('  PIKACHU  ');

        Http::assertSent(function ($request): bool {
            return str_contains((string) $request->url(), '/pokemon/pikachu');
        });
    }

    /** @test */
    public function test_handles_accented_names(): void
    {
        Http::fake([
            '*/pokemon/nidoran' => Http::response([
                'name'    => 'nidoran',
                'stats'   => [['base_stat' => 46, 'stat' => ['name' => 'hp']]],
                'sprites' => ['front_default' => 'https://example.com/nidoran.png'],
                'types'   => [['type' => ['name' => 'poison']]],
            ], 200),
        ]);

        $dto = $this->service->getPokemon('Nidorán');

        $this->assertInstanceOf(PokemonDTO::class, $dto);
        $this->assertSame(46, $dto->hp);

        Http::assertSent(function ($request): bool {
            return str_contains((string) $request->url(), '/pokemon/nidoran');
        });
    }
}
