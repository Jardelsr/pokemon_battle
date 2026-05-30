<?php

declare(strict_types=1);

namespace App\Exceptions;

class PokeApiException extends \RuntimeException
{
    public function __construct(
        string $message = 'O serviço Pokémon está temporariamente indisponível.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
