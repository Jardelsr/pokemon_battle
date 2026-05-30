<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PokemonDTO;
use App\Exceptions\PokeApiException;
use App\Exceptions\PokemonNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Str;

class PokeApiService
{
    private readonly string $baseUrl;
    private readonly int $timeout;

    public function __construct(private readonly HttpFactory $http)
    {
        $this->baseUrl = config('services.pokeapi.base_url');
        $this->timeout = config('services.pokeapi.timeout');
    }

    /**
     * Busca um Pokémon pelo nome na PokéAPI e retorna um DTO tipado.
     *
     * O nome é normalizado internamente (trim, lowercase, transliteração ASCII)
     * antes de ser enviado à API. A mensagem de erro, se houver, preserva o
     * nome original fornecido pelo usuário.
     *
     * @param  string  $name  Nome do Pokémon (aceita maiúsculas, acentos e espaços extras).
     *
     * @throws PokemonNotFoundException  Se a API retornar HTTP 404 para o nome informado.
     * @throws PokeApiException          Em falha de rede, timeout, resposta HTTP de erro
     *                                   ou payload com campos obrigatórios ausentes.
     */
    public function getPokemon(string $name): PokemonDTO
    {
        $originalName   = $name;
        $normalizedName = $this->normalizeName($name);

        try {
            $response = $this->http
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/pokemon/{$normalizedName}");
        } catch (ConnectionException $e) {
            throw new PokeApiException(previous: $e);
        }

        if ($response->status() === 404) {
            throw new PokemonNotFoundException($originalName);
        }

        if ($response->failed()) {
            throw new PokeApiException();
        }

        $data = $response->json();

        if (
            ! is_array($data)
            || ! isset($data['name'], $data['stats'], $data['sprites'], $data['types'])
            || ! is_array($data['stats'])
            || ! is_array($data['types'])
        ) {
            throw new PokeApiException('Resposta inesperada da PokéAPI.');
        }

        return PokemonDTO::fromApiResponse($data);
    }

    /**
     * Normaliza o nome do Pokémon para envio à PokéAPI.
     *
     * Remove espaços nas bordas, converte para minúsculas e transliterar
     * caracteres Unicode para seus equivalentes ASCII (ex: "Nidorán" → "nidoran").
     */
    private function normalizeName(string $name): string
    {
        return strtolower(trim(Str::ascii($name)));
    }
}
