<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando um ou mais Pokémon fornecidos não foram encontrados na PokéAPI.
 * Carrega um array de mensagens de erro indexadas pelo campo do formulário.
 */
class PokemonValidationException extends RuntimeException
{
    /**
     * @param string[] $messages Lista de mensagens de erro de validação.
     */
    public function __construct(private readonly array $messages)
    {
        parent::__construct(implode(' ', $messages));
    }

    /**
     * Retorna todas as mensagens de erro coletadas.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
