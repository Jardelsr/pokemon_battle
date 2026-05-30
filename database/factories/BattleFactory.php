<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Battle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Battle>
 */
class BattleFactory extends Factory
{
    protected $model = Battle::class;

    /**
     * Define the model's default state.
     *
     * Gera uma batalha com vencedor aleatório entre os dois Pokémon.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pokemonOne = fake()->randomElement([
            'bulbasaur', 'charmander', 'squirtle', 'pikachu',
            'gengar', 'machamp', 'alakazam', 'snorlax',
        ]);

        $pokemonTwo = fake()->randomElement([
            'mewtwo', 'raichu', 'charizard', 'blastoise',
            'venusaur', 'gyarados', 'dragonite', 'lapras',
        ]);

        $hpOne = fake()->numberBetween(30, 120);
        $hpTwo = fake()->numberBetween(30, 120);

        [$result, $winnerName] = match (true) {
            $hpOne > $hpTwo => ['pokemon_one_wins', $pokemonOne],
            $hpTwo > $hpOne => ['pokemon_two_wins', $pokemonTwo],
            default         => ['draw', null],
        };

        return [
            'pokemon_one_name' => $pokemonOne,
            'pokemon_one_hp'   => $hpOne,
            'pokemon_two_name' => $pokemonTwo,
            'pokemon_two_hp'   => $hpTwo,
            'winner_name'      => $winnerName,
            'result'           => $result,
        ];
    }

    /**
     * Estado em que o primeiro Pokémon vence.
     */
    public function pokemonOneWins(): static
    {
        return $this->state(fn (array $attributes) => [
            'pokemon_one_hp' => 100,
            'pokemon_two_hp' => 50,
            'winner_name'    => $attributes['pokemon_one_name'],
            'result'         => 'pokemon_one_wins',
        ]);
    }

    /**
     * Estado em que o segundo Pokémon vence.
     */
    public function pokemonTwoWins(): static
    {
        return $this->state(fn (array $attributes) => [
            'pokemon_one_hp' => 50,
            'pokemon_two_hp' => 100,
            'winner_name'    => $attributes['pokemon_two_name'],
            'result'         => 'pokemon_two_wins',
        ]);
    }

    /**
     * Estado de empate.
     */
    public function draw(): static
    {
        return $this->state([
            'pokemon_one_hp' => 75,
            'pokemon_two_hp' => 75,
            'winner_name'    => null,
            'result'         => 'draw',
        ]);
    }
}
