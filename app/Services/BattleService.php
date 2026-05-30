<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\BattleResultDTO;
use App\DTOs\PokemonDTO;
use App\Models\Battle;
use Illuminate\Database\Eloquent\Collection;

class BattleService
{
    public function __construct(
        private readonly PokeApiService $pokeApiService,
        private readonly Battle $battleModel,
    ) {
    }

    /**
     * Executa uma batalha entre dois Pokémon, persiste o resultado e retorna o DTO.
     *
     * A lógica de vitória é baseada exclusivamente no HP base:
     *  - HP pokemonOne > HP pokemonTwo → 'pokemon_one_wins'
     *  - HP pokemonTwo > HP pokemonOne → 'pokemon_two_wins'
     *  - HPs iguais                   → 'draw' (winnerName = null)
     *
     * Exceptions lançadas pelo PokeApiService propagam sem serem capturadas aqui.
     *
     * @param  string  $pokemonOneName  Nome do primeiro Pokémon (ex: "pikachu").
     * @param  string  $pokemonTwoName  Nome do segundo Pokémon (ex: "raichu").
     *
     * @throws \App\Exceptions\PokemonNotFoundException  Se qualquer um dos nomes for inválido.
     * @throws \App\Exceptions\PokeApiException          Em falha de rede ou resposta inválida.
     */
    public function battle(string $pokemonOneName, string $pokemonTwoName): BattleResultDTO
    {
        $pokemonOne = $this->pokeApiService->getPokemon($pokemonOneName);
        $pokemonTwo = $this->pokeApiService->getPokemon($pokemonTwoName);

        [$result, $winnerName] = $this->determineResult($pokemonOne, $pokemonTwo);

        $this->battleModel->create([
            'pokemon_one_name' => $pokemonOne->name,
            'pokemon_one_hp'   => $pokemonOne->hp,
            'pokemon_two_name' => $pokemonTwo->name,
            'pokemon_two_hp'   => $pokemonTwo->hp,
            'winner_name'      => $winnerName,
            'result'           => $result,
        ]);

        return new BattleResultDTO(
            pokemonOne: $pokemonOne,
            pokemonTwo: $pokemonTwo,
            winnerName: $winnerName,
            result:     $result,
        );
    }

    /**
     * Retorna as batalhas mais recentes ordenadas por data de criação decrescente.
     *
     * @param  int  $limit  Número máximo de registros (padrão: 5).
     *
     * @return Collection<int, Battle>
     */
    public function getRecentBattles(int $limit = 5): Collection
    {
        return $this->battleModel->latest()->limit($limit)->get();
    }

    /**
     * Compara os HPs e retorna o resultado e o nome do vencedor.
     *
     * @return array{0: string, 1: string|null}  [$result, $winnerName]
     */
    private function determineResult(PokemonDTO $pokemonOne, PokemonDTO $pokemonTwo): array
    {
        if ($pokemonOne->hp > $pokemonTwo->hp) {
            return ['pokemon_one_wins', $pokemonOne->name];
        }

        if ($pokemonTwo->hp > $pokemonOne->hp) {
            return ['pokemon_two_wins', $pokemonTwo->name];
        }

        return ['draw', null];
    }
}
