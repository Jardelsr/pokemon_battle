<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class PokemonDTO
{
    public function __construct(
        public string $name,
        public int    $hp,
        public string $spriteUrl,
        public array  $types,
        public array  $stats,
    ) {
    }

    /**
     * Constrói um PokemonDTO a partir do payload bruto da PokéAPI.
     *
     * Exemplo de mapeamento:
     *  - name      ← $data['name']
     *  - hp        ← $data['stats'][]['base_stat'] onde stat.name === 'hp'
     *  - spriteUrl ← $data['sprites']['front_default']
     *  - types     ← $data['types'][]['type']['name']   (ex: ['electric'])
     *  - stats     ← todos os stats como ['hp' => 35, 'attack' => 55, ...]
     *
     * @param  array<string, mixed>  $data  Payload JSON decodificado de /pokemon/{name}
     *
     * @throws \InvalidArgumentException Se o campo 'stats' ou 'sprites' estiver ausente.
     */
    public static function fromApiResponse(array $data): self
    {
        $hp = 0;

        foreach ($data['stats'] as $statEntry) {
            if ($statEntry['stat']['name'] === 'hp') {
                $hp = (int) $statEntry['base_stat'];
                break;
            }
        }

        $types = array_map(
            static fn(array $typeEntry): string => $typeEntry['type']['name'],
            $data['types'],
        );

        $stats = [];

        foreach ($data['stats'] as $statEntry) {
            $stats[$statEntry['stat']['name']] = (int) $statEntry['base_stat'];
        }

        return new self(
            name:      (string) $data['name'],
            hp:        $hp,
            spriteUrl: (string) ($data['sprites']['front_default'] ?? ''),
            types:     array_values($types),
            stats:     $stats,
        );
    }
}
