<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class BattleResultDTO
{
    public function __construct(
        public PokemonDTO $pokemonOne,
        public PokemonDTO $pokemonTwo,
        public ?string    $winnerName,
        public string     $result,
    ) {
    }

    /**
     * Indica se a batalha terminou em empate.
     */
    public function isDraw(): bool
    {
        return $this->result === 'draw';
    }

    /**
     * Retorna o PokemonDTO vencedor, ou null em caso de empate.
     */
    public function getWinner(): ?PokemonDTO
    {
        return match ($this->result) {
            'pokemon_one_wins' => $this->pokemonOne,
            'pokemon_two_wins' => $this->pokemonTwo,
            default            => null,
        };
    }
}
