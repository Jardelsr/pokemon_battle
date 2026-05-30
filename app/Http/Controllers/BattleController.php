<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PokeApiException;
use App\Exceptions\PokemonNotFoundException;
use App\Services\BattleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BattleController extends Controller
{
    public function __construct(private readonly BattleService $battleService)
    {
    }

    /**
     * Exibe o formulário de batalha e o histórico recente.
     */
    public function index(): View
    {
        $recentBattles = $this->battleService->getRecentBattles();

        return view('battle.index', compact('recentBattles'));
    }

    /**
     * Processa a batalha entre dois Pokémon e exibe o resultado.
     */
    public function battle(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'pokemon_one' => ['required', 'string', 'min:1', 'max:100'],
            'pokemon_two' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        try {
            $result = $this->battleService->battle(
                $validated['pokemon_one'],
                $validated['pokemon_two'],
            );
        } catch (PokemonNotFoundException $e) {
            return back()
                ->withInput()
                ->withErrors(['battle' => $e->getMessage()]);
        } catch (PokeApiException $e) {
            return back()
                ->withInput()
                ->withErrors(['battle' => $e->getMessage()]);
        }

        return view('battle.result', compact('result'));
    }
}
