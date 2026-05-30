<?php

declare(strict_types=1);

namespace App\Exceptions;

class PokemonNotFoundException extends \Exception
{
    public function __construct(public readonly string $pokemonName)
    {
        parent::__construct("Pokémon '{$pokemonName}' não encontrado.");
    }
}
